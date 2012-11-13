<?php

    // Стартуем сессию с пользователем - сделать доступными переменные сессии
    session_start();

    // Подключаем нужные модели и представления
    include '../models/GlobFunc.php';
    include '../models/Logger.php';
    include '../models/IncomingUser.php';

    // Создаем объект-хранилище глобальных функций
    $globFunc = new GlobFunc();

    // Подключаемся к БД
    $DBlink = $globFunc->connectToDB();
    // Удалось ли подключиться к БД?
    if ($DBlink == FALSE) die('Ошибка подключения к базе данных (. Попробуйте зайти к нам немного позже.');

    // Инициализируем модель для запросившего страницу пользователя
    $incomingUser = new IncomingUser($globFunc, $DBlink);

    // Задаем список допустимых расширений для загружаемых файлов. Также расширение при начале загрузки проверяется в js файле vendor\fileuploader.js. Списки должны совпадать
    $allowedExtensions = array("jpeg", "JPEG", "jpg", "JPG", "png", "PNG", "gif", "GIF");
    // Задаем максимальный размер файла для загрузки в байтах
    $sizeLimit = 25 * 1024 * 1024;

    $uploader = new qqFileUploader($allowedExtensions, $sizeLimit, $DBlink);

    // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
    $result = $uploader->handleUpload('../uploaded_files/', FALSE);

    // Закрываем соединение с БД
    $globFunc->closeConnectToDB($DBlink);

    // to pass data through iframe you will need to encode all html tags
    echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

    /******************************************/


    /**
     * Handle file uploads via XMLHttpRequest
     */
    class qqUploadedFileXhr
    {
        /**
         * Save the file to the specified path
         * @return boolean TRUE on success
         */
        function save($path)
        {
            $input = fopen("php://input", "r");
            $temp = tmpfile();
            $realSize = stream_copy_to_stream($input, $temp);
            fclose($input);

            if ($realSize != $this->getSize()) {
                return FALSE;
            }

            $target = fopen($path, "w");
            fseek($temp, 0, SEEK_SET);
            stream_copy_to_stream($temp, $target);
            fclose($target);

            return TRUE;
        }

        function getName()
        {
            return $_GET['qqfile'];
        }

        function getSize()
        {
            if (isset($_SERVER["CONTENT_LENGTH"])) {
                return (int)$_SERVER["CONTENT_LENGTH"];
            } else {
                throw new Exception('Getting content length is not supported.');
            }
        }
    }

    /**
     * Handle file uploads via regular form post (uses the $_FILES array)
     */
    class qqUploadedFileForm
    {
        /**
         * Save the file to the specified path
         * @return boolean TRUE on success
         */
        function save($path)
        {
            if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
                return FALSE;
            }
            return TRUE;
        }

        function getName()
        {
            return $_FILES['qqfile']['name'];
        }

        function getSize()
        {
            return $_FILES['qqfile']['size'];
        }
    }

    class qqFileUploader
    {
        private $allowedExtensions = array(); // Хранит ограничение на расширения файлов для загрузки. По умолчанию - нет ограничений
        private $sizeLimit = 10485760; // Хранит ограничение на максимальный объем файла для загрузки. По  умолчанию - 10485760 байт
        private $file;
        private $DBlink = FALSE;

        function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760, $DBlink = FALSE)
        {
            $allowedExtensions = array_map("strtolower", $allowedExtensions);

            $this->allowedExtensions = $allowedExtensions;
            $this->sizeLimit = $sizeLimit;
            $this->DBlink = $DBlink;

            $this->checkServerSettings();

            if (isset($_GET['qqfile'])) {
                $this->file = new qqUploadedFileXhr();
            } elseif (isset($_FILES['qqfile'])) {
                $this->file = new qqUploadedFileForm();
            } else {
                $this->file = FALSE;
            }
        }

        public function getName()
        {
            if ($this->file) return $this->file->getName(); else return FALSE;
        }

        // Проверяет настройки сервера - чтобы они были не более строгими, чем заданная мной максимальная величина файла для загрузки
        private function checkServerSettings()
        {
            $postSize = $this->toBytes(ini_get('post_max_size'));
            $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

            if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
                $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
                die("{'error':'Ошибка на сервере. Необходимо увеличить post_max_size и upload_max_filesize до $size, $postSize, $uploadSize'}");
            }
        }

        // Возвращает значение в байтах, конвертируя Гигабайты, Мегабайты и Килобайты
        private function toBytes($str)
        {
            $val = trim($str);
            $last = strtolower($str[strlen($str) - 1]);
            switch ($last) {
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }
            return $val;
        }

        /**
         * Обработчик загрузки файла
         * Возвращает массив ('success'=>true) или массив ('error'=>'error message')??
         */
        function handleUpload($uploadDirectory, $replaceOldFile = FALSE)
        {

            /****************************************************************************************************************************
             * Предварительная подготовка к обработке фотографии, загруженной пользователем
             ***************************************************************************************************************************/

            // Проверяем каталог для хранения файлов на право записи в него
            if (!is_writable($uploadDirectory)) {
                return array('error' => "Ошибка на сервере. Директория для сохранения файлов не доступна для записи. $uploadDirectory");
            }

            // Проверяем - есть ли файл, который мы можем обработать
            if (!$this->file) {
                return array('error' => 'Файл на был загружен на сервер');
            }

            // Серверная проверка размера файла
            $size = $this->file->getSize();
            if ($size == 0) {
                return array('error' => 'Файл имеет нулевой размер');
            }
            if ($size > $this->sizeLimit) {
                return array('error' => 'Файл имеет слишком большой размер');
            }

            // Вычисляем новое уникальное имя для файла
            $pathinfo = pathinfo($this->file->getName());
            //$filename = $pathinfo['filename'];
            $filename = md5(uniqid()); //TODO: сделать равномерное случайное число (чтобы файлы равномерно распределеялись по каталогам при сохранении на сервер)
            $ext = @$pathinfo['extension']; // hide notices if extension is empty

            // Серверная проверка расширения файла
            if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
                $these = implode(', ', $this->allowedExtensions);
                return array('error' => 'Файл имеет неподдерживаемое расширение. Список поддерживаемых форматов: ' . $these . '.');
            }

            // Если в каталоге файл с таким именем уже существует - дополняем имя нового файла для того, чтобы сохранить его уникальность
            if (!$replaceOldFile) {
                /// Если запрещено перезаписывать файлы, загруженные ранее
                while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                    $filename .= rand(10, 99);
                }
            }

            // Сохраняем файл в каталог file_uploaded для дальнейшей обработки
            $tempFilePath = $uploadDirectory . $filename . '.' . $ext;
            if (!$this->file->save($tempFilePath)) {
                return array('error'=> 'Не удалось сохранить загруженный файл на сервере.' . 'Загрузка была прервана, либо произошла ошибка на сервере.');
            }

            /****************************************************************************************************************************
             * Создаем и сохраняем на сервере фотографии нужного формата (small, middle, big) из исходной (загруженной пользователем)
             ***************************************************************************************************************************/

            // Определяем размеры и тип исходной фотографии
            $srcParams = getimagesize($tempFilePath);

            // Получим ресурс, соответствующий исходной фотографии для дальнейшего преобразования его в нужные типы фотографий (small, middle, big)
            $foto_src = $this->getSrcImage($srcParams, $tempFilePath);
            if (!$foto_src) {
                return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Формат загруженного файла не входит в список поддерживаемых: ' . 'gif, jpeg, png');
            };

            // Производим преобразование исходной фотографии в целевую большую
            $foto_dst = $this->getReSizeImage($foto_src, $srcParams, "big");
            if (!$foto_dst) {
                return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при преобразовании исходной фотографии в jpeg большого размера. Попробуйте еще раз, либо загрузите другую фотографию');
            }

            if (!$saveRes = $this->saveImg($foto_dst, $uploadDirectory, "big", $filename)) {
                return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при записи в каталог хранения фотографии большого размера. Попробуйте еще раз, либо загрузите другую фотографию');
            }

            // Производим преобразование исходной фотографии в целевую среднюю
            $foto_dst = $this->getReSizeImage($foto_src, $srcParams, "middle");
            if (!$foto_dst) return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при преобразовании исходной фотографии в jpeg среднего размера. Попробуйте еще раз, либо загрузите другую фотографию');

            if (!$saveRes = $this->saveImg($foto_dst, $uploadDirectory, "middle", $filename)) {
                return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при записи в каталог хранения фотографии среднего размера. Попробуйте еще раз, либо загрузите другую фотографию');
            }

            // Производим преобразование исходной фотографии в целевую маленькую
            $foto_dst = $this->getReSizeImage($foto_src, $srcParams, "small");
            if (!$foto_dst) return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при преобразовании исходной фотографии в jpeg маленького размера. Попробуйте еще раз, либо загрузите другую фотографию');

            if (!$saveRes = $this->saveImg($foto_dst, $uploadDirectory, "small", $filename)) {
                return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при записи в каталог хранения фотографии маленького размера. Попробуйте еще раз, либо загрузите другую фотографию');
            }

            // Сохраняем информацию о фотографиях в Базу данных
            $res = $this->saveInfToDB($saveRes['folder'], $filename, $size);

            if ($res == FALSE) {
                return array('error' => 'Не удалось сохранить загруженный файл на сервере.' . ' Ошибка при сохранении информации в базу данных. Попробуйте еще раз, либо загрузите другую фотографию');
            }

            $this->clearMemory($foto_dst, $foto_src, $tempFilePath);

            /****************************************************************************************************************************
             * Выдача положительного результата и полезных для JS на клиенте данных
             ***************************************************************************************************************************/

            return array('success' => TRUE,
                         'folder'  => $saveRes['folder'], // Папка, в которой сохранена фотография, вида: "uploaded_files/6"
                         'name'    => $filename, // Идентификатор фотографии, который также служит уникальным именем файла (без расширения)
                         'ext'     => $saveRes['ext']); // Расширение, под которым сохранены все 3 копии файла (small, medium, big)

        }

        /****************************************************************************************************************************
         * Набор методов для обработки фотографий
         ***************************************************************************************************************************/

        // Функция по типу исходного файла фотографии создает ресурс для обработки
        // Кроме того эта функция не позволяет загрузить на сервер файл, не являющийся фотографией, т.е. защищает например от банального изменения расширения для вредоносного файла
        function getSrcImage($srcParams, $tempFilePath)
        {
            // Вычисляем тип (расширении) фотографии пользователя и вызываем соответствующую функцию для создания обрабатываемого с помощью php изображения
            switch (strtolower($srcParams['mime'])) {
                case "image/gif":
                    $foto_src = imagecreatefromgif($tempFilePath);
                    break;
                case "image/jpeg":
                    $foto_src = imagecreatefromjpeg($tempFilePath);
                    break;
                case "image/png":
                    $foto_src = imagecreatefrompng($tempFilePath);
                    break;
                /*case "image/bmp":
            case "image/x-ms-bmp":
                $foto_src = imagecreatefromwbmp($tempFilePath);
                break;*/
                default:
                    return FALSE;
            }

            return $foto_src;
        }

        // Функция возвращает фотографию с измененными размерами. Размеры изменены в соответствии с указанным типом назначения
        function getReSizeImage($foto_src, $srcParams, $type)
        {
            // Определяем размеры исходной фотографии
            $srcWidth = $srcParams[0]; // Получаем ширину исходной загруженной пользователем фотографии
            $srcHeight = $srcParams[1]; // Получаем высоту исходной загруженной пользователем фотографии

            // Инициализируем размеры для новой фотографии
            $new_width = $srcWidth;
            $new_height = $srcHeight;

            // Определяем максимальные размеры целевой фотографии - в зависимости от типа
            if ($type == "big") {

                $new_width_max = 900;
                $new_height_max = 900;

            } elseif ($type == "middle") {

                $new_width_max = 300;
                $new_height_max = 300;

            } elseif ($type == "small") {

                $new_width_max = 120;
                $new_height_max = 120;

            } else {

                return FALSE;

            }

            // Вычисляем целевые размеры изображения
            if ($srcWidth > $new_width_max) {
                $ratio = $srcWidth / $new_width_max;
                $new_width = $new_width_max;
                $new_height = $srcHeight / $ratio;
            }
            if ($new_height > $new_height_max) {
                $ratio = $new_height / $new_height_max;
                $new_height = $new_height_max;
                $new_width = $new_width / $ratio;
            }

            // Инициализируем целевое изображение
            // функция  imagecreatetruecolor создает пустое полноцветное изображение размерами $new_width и $new_height.
            // Созданное изображение имеет черный фон.
            $foto_dst = imagecreatetruecolor($new_width, $new_height);

            // Непосредственное получение целевой фотографии из исходной
            imagecopyresampled($foto_dst, $foto_src, 0, 0, 0, 0, $new_width, $new_height, $srcWidth, $srcHeight);

            return $foto_dst;
        }

        // Функция сохраняет в нужный каталог файл фотографии, а также записывает в БД данные о нем
        function saveImg($foto_dst, $uploadDirectory, $type, $filename)
        {
            $subDirectory = substr($filename, 0, 1);
            $typeDirectory = "/" . $type . "/";

            // Вычисляем адрес для сохранения целевой фотографии ($foto_dst)
            // Для того, чтобы не складывать все фотографии в один каталог (при достижении 3-4 тыс. файлов все будет сильно тормозить) я делаю следующее: по первому символу в id(названии) файла определяю каталог для хранения внутри каталога file_upload. Внутри найденного каталога определяю еще один каталог, соответствующий размеру фотографии (small, middle, big)
            $urlForSave = $uploadDirectory . $subDirectory . $typeDirectory . $filename . '.' . "jpeg";

            // Сохранение целевой фотографии в формате jpeg в целевой каталог
            if (imagejpeg($foto_dst, $urlForSave, 85)) {

                // Преобразуем $uploadDirectory = '../uploaded_files/' к виду, который нужно сохранить в БД. То есть путь нужно показать относительно корня проекта, а не относительно текущего каталога расположения uploader.php
                $uploadDirectoryFromCore = substr($uploadDirectory, 3);

                return array('folder' => $uploadDirectoryFromCore . $subDirectory,
                             'ext'    => "jpeg");

            } else {

                return FALSE;

            }
        }

        // Функция сохраняет данные о файле фотографии в Базу данных - в таблицу tempFotos
        function saveInfToDB($folder, $filename, $size) {

            // Проверяем, что есть соединение с БД
            if ($this->DBlink == FALSE) return FALSE;

            // Готовим данные для сохранения в БД
            $sizeMb = round($size / 1024 / 1024, 1);
            $extension = 'jpeg';

            // Сохраняем информацию о загруженной фотке в БД
            $stmt = $this->DBlink->stmt_init();
            if (($stmt->prepare("INSERT INTO tempfotos (id, fileUploadId, folder, filename, extension, filesizeMb) VALUES (?,?,?,?,?,?)") === FALSE)
                OR ($stmt->bind_param("sssssd", $filename, $_GET['fileuploadid'], $folder, $_GET['sourcefilename'], $extension, $sizeMb) === FALSE)
                OR ($stmt->execute() === FALSE)
                OR (($res = $stmt->affected_rows) === -1)
                OR ($res === 0)
                OR ($stmt->close() === FALSE)
            ) {
                // TODO: Сохранить в лог ошибку работы с БД ($stmt->errno . $stmt->error)
                return FALSE;
            }

            return TRUE;

        }

        // Функция для очистки памяти
        function clearMemory($foto_dst, $foto_src, $tempFilePath)
        {
            imagedestroy($foto_dst);
            imagedestroy($foto_src);
            unlink($tempFilePath);
        }

    }
