<?php

/* Класс для логирования событий в текстовый файл
* Для вызова логирования в коде используется конструкция:
* Logger::getLogger($name)->log($data); в качестве $data передаем информацию, которуб нужно записать в файл. Время записи добавиться автоматически
* Пример строки для логирования со страницы personal.php: "Logger::getLogger(GlobFunc::$loggerName)->log("Ошибка!!!!!!!!!!!");"
*/

class Logger {
    public static $PATH = "logs"; // Адрес для папки с лог файлами. Относительно той страницы, на которой происходит обращение к логированию. Например, при логировании события на странице personal.php (находится в корне), обращение будет к каталогу: корень/logs
    protected static $loggers = array(); // Массив с разными файлами логгеров

    protected $name; // Имя текущего логгера
    protected $file; // Путь к файлу, с которым он работает
    protected $fp; // Файловый поток, через который осуществляется запись

    // КОНСТРУКТОР
    // Конструктор будет использоваться внутри класса, непосредственно при логировании мы будем пользоваться функцией getLogger
    public function __construct($name, $file = NULL) {

        if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "") $websiteRoot = $_SERVER['DOCUMENT_ROOT']; else $websiteRoot = "/var/www/dimau/data/www/svobodno.org"; // так как cron не инициализирует переменную окружения $_SERVER['DOCUMENT_ROOT'] (а точнее инициализирует ее пустой строкой), приходиться использовать костыль
        Logger::$PATH = $websiteRoot . '/logs';
        $this->name = $name;
        $this->file = $file;

        $this->open();
    }

    // ДЕСТРУКТОР
    public function __destruct() {
        fclose($this->fp);
    }

    // Метод инициализирует файловый поток. Если переменная $file не задана, то будет открыт файл с тем же именем, что и логгер.
    public function open() {
        if (self::$PATH == null) {
            return;
        }

        $this->fp = fopen($this->file == null ? self::$PATH . '/' . $this->name . '.log' : self::$PATH . '/' . $this->file, 'a+');
    }

    /**
     * Функция возвращает нам логгер, имя которого мы указали
     * @param string $name имя логгера, который нужно вернуть
     * @param null|string $file имя файла логгера, который нужно создать/вернуть
     * @return Logger возвращает объект класса Logger
     */
    public static function getLogger($name = 'root', $file = null) {
        if (!isset(self::$loggers[$name])) {
            self::$loggers[$name] = new Logger($name, $file);
        }

        return self::$loggers[$name];
    }

    // Метод заносит в лог файл сообщение, переданное в качестве аргумента
    public function log($message) {
        if (!is_string($message)) {
            // если мы хотим вывести, к примеру, массив
            $this->logPrint($message);
            return;
        }

        $log = '';
        $log .= "\r\n"; // Добавим перенос строки для виндовс (смотреть в блокноте)
        // зафиксируем дату и время происходящего
        $currentDate = new DateTime(NULL, new DateTimeZone('Asia/Yekaterinburg'));
        $currentDate = $currentDate->format("D M d H:i:s Y");
        $log .= "[" . $currentDate . "] ";
        // если мы отправили в функцию больше одного параметра,
        // выведем их тоже
        if (func_num_args() > 1) {
            $params = func_get_args();

            $message = call_user_func_array('sprintf', $params);
        }

        $log .= $message;
        // запись в файл
        $this->_write($log);
    }

    public function logPrint($obj) {
        // заносим все выводимые данные в буфер
        ob_start();

        print_r($obj);
        // очищаем буфер
        $ob = ob_get_clean();

        // записываем
        $this->log($ob);
    }

    // Метод осуществляет непосредственную запись в файл лоигруемой строки
    protected function _write($string) {
        fwrite($this->fp, $string);
    }

}
