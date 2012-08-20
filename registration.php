<?php
//ini_set ("session.use_trans_sid", true); вроде как PHP сам умеет устанавливать id сессии либо в куки, либо в строку запроса (http://www.phpfaq.ru/sessions)
session_start();
include_once 'lib/connect.php'; //подключаемся к БД
include_once 'lib/function_global.php'; //подключаем библиотеку функций
$correct = null; // Инициализируем переменную корректности - нужно для того, чтобы не менять лишний раз идентификатор в input hidden у фотографий

//проверим, быть может пользователь уже авторизирован. Если это так, перенаправим его на главную страницу сайта
if (isset($_SESSION['id']) || (isset($_COOKIE['login']) && isset($_COOKIE['password'])))
{
    header('Location: index.php');
}
else
{
    if (isset($_POST['readyButton'])) //если была нажата кнопка регистрации, проверим данные на корректность и, если данные введены и введены правильно, добавим запись с новым пользователем в БД
    {
        $errors = registrationCorrect(); //записываем в переменную результат работы функции registrationCorrect(), которая возвращает пустой array, если введённые данные верны и array с ошибками в противном случае

        // Считаем ошибки, если 0, то можно будет записать данные в БД
        if (count($errors) == 0)
        {
            $correct = true;
        }
        else
        {
            $correct = false;
        }

        // Формируем набор переменных для сохранения в базу данных, либо для возвращения вместе с формой при их некорректности
        $name = htmlspecialchars($_POST['name']);
        $secondName = htmlspecialchars($_POST['secondName']);
        $surname = htmlspecialchars($_POST['surname']);
        $sex = htmlspecialchars($_POST['sex']);
        $nationality = htmlspecialchars($_POST['nationality']);
        $birthday = htmlspecialchars($_POST['birthday']);
        $login = htmlspecialchars($_POST['login']);
        $password = htmlspecialchars($_POST['password']);
        $telephon = htmlspecialchars($_POST['telephon']);
        $email = htmlspecialchars($_POST['email']);

        $fileUploadId = $_POST['fileUploadId'];
        if ($rez = mysql_query("SELECT filename FROM tempregfotos WHERE fileUploadId = $fileUploadId")) // ищем уже загруженные пользователем фотки
        {
            $row = mysql_fetch_assoc($rez);
            $numUploadedFiles = mysql_num_rows($rez);
            $row["filename"];
        }




        if ($correct) //если данные верны, запишем их в базу данных
        {
            $login = htmlspecialchars($login);
            $salt = mt_rand(100, 999);
            $tm = time();
            $password = md5(md5($password).$salt);

            if (mysql_query("INSERT INTO users (login,password,salt,reg_date,last_act) VALUES ('".$login."','".$password."','".$salt."','".$tm."','".$tm."')")) //пишем данные в БД и авторизовываем пользователя
            {
                setcookie ("login", $login, time() + 50000, '/');
                setcookie ("password", md5($login.$password), time() + 50000, '/');
                $rez = mysql_query("SELECT * FROM users WHERE login=".$login);
                @$row = mysql_fetch_assoc($rez);
                $_SESSION['id'] = $row['id'];
                $regged = true;
                header('Location: successfullRegistration.php'); //после успешной регистрации - переходим на соответствующую страницу
            }
        }
        else
        {
            //exit("данные не верны!"); // действия в случае некорректности данных
        }
    }
    else
    {
        $name = "";
        $secondName = "";
        $surname = "";
        $sex = "0";
        $nationality = "0";
        $birthday = "";
        $login = "";
        $password = "";
        $telephon = "";
        $email = "";
        $fileUploadId = generateCode(7);
    }
}
?>

<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!-- Consider specifying the language of your content by adding the `lang` attribute to <html> -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
	<!--<![endif]-->
	<head>

		<!--

		Если запрос = registration.php?type=tenant, то php должен сформировать форму без вкладки Мои объявления
		Если запрос = registration.php?type=owner, то php должен сформировать форму без вкладки Условий поиска
		Если запрос = registration.php, то выдаем страницу со всеми вкладками

		-->
		<meta charset="utf-8">

		<!-- Use the .htaccess and remove these lines to avoid edge case issues.
		More info: h5bp.com/i/378 -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Форма регистрации</title>
		<meta name="description" content="Форма регистрации">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
        <link rel="stylesheet" href="css/fileuploader.css">
        <link rel="stylesheet" href="css/main.css">
		<style>
			/* Стили для капчи и для Готово */
			.capcha {
				margin: 10px 10px 0px 20px;
				float: right;
			}

			.readyButton {
				float: right;
				margin: 10px 10px 0px 10px;
			}

            /* Стили для страницы социальных сетей*/
            #tabs-3 .searchItem {
                line-height: 2.8;
            }

            #tabs-3 .searchItemBody {
                margin-left: 10px;
            }

            #tabs-3 .searchItemBody input, #tabs-3 .searchItemBody img  {
                vertical-align: middle;
            }

		</style>

		<!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

		<!-- All JavaScript at the bottom, except this Modernizr build.
		Modernizr enables HTML5 elements & feature detects for optimal performance.
		Create your own custom Modernizr build: www.modernizr.com/download/ -->
		<script src="js/vendor/modernizr-2.5.3.min.js"></script>

	</head>
	<body>
		<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
		chromium.org/developers/how-tos/chrome-frame-getting-started -->
		<!--[if lt IE 7]><p class="chromeframe">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

		<!-- Add your site or application content here -->
		<div class="page_without_footer">

        <!-- Сформируем и вставим заголовок страницы -->
        <?php
        include("header.php");
        ?>

			<div class="page_main_content">

				<div class="wrapperOfTabs">
					<form name="personalInformation" method="post" enctype="multipart/form-data">
						<div class="headerOfPage">
							Зарегистрируйтесь
						</div>

						<div id="tabs">
							<ul>
								<li>
									<a href="#tabs-1">Личные данные</a>
								</li>
                                <li>
                                    <a href="#tabs-2">Образование / Работа</a>
                                </li>
                                <li>
                                    <a href="#tabs-3">Социальные сети</a>
                                </li>
								<li>
									<a href="#tabs-4">Что ищете?</a>
								</li>
							</ul>
							<div id="tabs-1">
								<div class="shadowText">
									Информация, указаннная при регистрации, необходима для того, чтобы представить Вас собственникам тех объектов, которыми Вы заинтересутесь. Заполните форму на этой и следующих вкладках как можно подробнее.
									<br>
									<span class="required">* </span> - обязательное для заполнения поле
								</div>
								<div class="descriptionFieldsetsWrapper">
									<fieldset class="edited private">
										<legend>
											ФИО
										</legend>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Имя: </span>
											<div class="searchItemBody">
												<input name="name" type="text" size="38" autofocus <?php echo "value='$name'";?>>
											</div>
										</div>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Отчество: </span>
											<div class="searchItemBody">
												<input name="secondName" type="text" size="33" <?php echo "value='$secondName'";?>>
											</div>
										</div>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Фамилия: </span>
											<div class="searchItemBody">
												<input name="surname" type="text" size="33" <?php echo "value='$surname'";?>>
											</div>
										</div>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Пол: </span>
											<div class="searchItemBody">
												<select name="sex">
													<option value="0" <?php if ($sex == "0") echo "selected";?>></option>
													<option value="man" <?php if ($sex == "man") echo "selected";?>>мужской</option>
													<option value="woman" <?php if ($sex == "woman") echo "selected";?>>женский</option>
												</select>
											</div>
										</div>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Национальность: </span>
											<div class="searchItemBody">
												<select name="nationality">
													<option value="0" <?php if ($nationality == "0") echo "selected";?>></option>
													<option value="1" <?php if ($nationality == "1") echo "selected";?>>русский</option>
													<option value="2" <?php if ($nationality == "2") echo "selected";?>>европеец, американец</option>
													<option value="3" <?php if ($nationality == "3") echo "selected";?>>СНГ, восточная нац-сть</option>
												</select>
											</div>
										</div>
										<div class="searchItem"> <!-- TODO: поменять контроль поля при подключении календаря -->
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">День рождения: </span>
											<div class="searchItemBody">
                                                <input name="birthday" type="text" id="datepicker" size="15" placeholder="дд.мм.гггг" <?php echo "value='$birthday'";?>>
											</div>
										</div>
									</fieldset>

									<div style="display: inline-block; vertical-align: top;">
										<fieldset class="edited private" style="display: block;">
											<legend>
												Логин и пароль
											</legend>
											<div class="searchItem" title="Используйте в качестве логина ваш e-mail или телефон">
												<div class="required">
													*
												</div>
												<span class="searchItemLabel">Логин: </span>
												<div class="searchItemBody">
													<input type="text" size="30" maxlength="50" name="login" <?php echo "value='$login'";?>>
												</div>
											</div>
											<div class="searchItem">
												<div class="required">
													*
												</div>
												<span class="searchItemLabel">Пароль: </span>
												<div class="searchItemBody">
													<input type="password" size="29" maxlength="50" name="password" <?php echo "value='$password'";?>>
												</div>
											</div>
										</fieldset>

										<fieldset class="edited private" style="display: block;">
											<legend>
												Контакты
											</legend>
											<div class="searchItem">
												<div class="required">
													*
												</div>
												<span class="searchItemLabel">Телефон: </span>
												<div class="searchItemBody">
													<input name="telephon" type="text" size="27" <?php echo "value='$telephon'";?>>
												</div>
											</div>
											<div class="searchItem">
												<div class="required">
													*
												</div>
												<span class="searchItemLabel">e-mail: </span>
												<div class="searchItemBody">
													<input name="email" type="text" size="30" <?php echo "value='$email'";?>>
												</div>
											</div>
										</fieldset>
									</div>

									<!--

									Кроме того, для собственников не нужно передавать блоки Образование и Работа, Коротко о себе
									Также для собственника не формируется вкладка Условия поиска
									Фото становится необязательным - убрать звездочку

									Сделать проверку перед отправкой и серверную часть капчи

									Но собственнику отправляется дополнительно

									-->

									<fieldset class="edited private" style="min-width: 300px;">
										<legend title="Для успешной регистрации должна быть загружена хотя бы 1 фотография">
											<div class="required">
												*
											</div>
											Фотографии
										</legend>
                                        <input type="hidden" name="fileUploadId" id="fileUploadId" <?php echo "value='$fileUploadId'";?>>
                                        <div id="file-uploader">
                                            <noscript>
                                                <p>Пожалуйста, активируйте JavaScript для загрузки файлов</p>
                                                <!-- or put a simple form for upload here -->
                                            </noscript>
                                        </div>
									</fieldset>

								</div><!-- /end.descriptionFieldsetsWrapper -->
								<div class="shadowText" style="margin-top: 7px;">
									По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
								</div>
							</div>
                            <div id="tabs-2">
                                <div class="shadowText">
                                    Данные об образовании и работе арендатора - одни из самых востребованных для любого собственника жилья. Эта информация предоставляется собственникам только тех объектов, которыми Вы заинтересуетесь.
                                </div>
                                <fieldset class="edited private">
                                    <legend>
                                        Образование
                                    </legend>
                                    <div class="searchItem" title="Укажите курс, на котором учитесь, или год окончания, если Вы уже закончили учебное заведение">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Текущий статус: </span>
                                        <div class="searchItemBody">
                                            <select name="currentStatusEducation" id="currentStatusEducation">
                                                <option value="0" selected></option>
                                                <option value="1">Нигде не учился</option>
                                                <option value="2">Сейчас учусь</option>
                                                <option value="3">Закончил</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="almamater" class="searchItem ifLearned" title="Укажите учебное заведение, в котором учитесь сейчас, либо последнее из тех, что заканчивали">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Учебное заведение: </span>
                                        <div class="searchItemBody">
                                            <input class="ifLearned" type="text" size="50">
                                        </div>
                                    </div>
                                    <div id="speciality" class="searchItem ifLearned">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Специальность: </span>
                                        <div class="searchItemBody">
                                            <input class="ifLearned" type="text" size="55">
                                        </div>
                                    </div>
                                    <div id="kurs" class="searchItem ifLearned" title="Укажите курс, на котором учитесь">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Курс: </span>
                                        <div class="searchItemBody">
                                            <input class="ifLearned" type="text" size="19">
                                        </div>
                                    </div>
                                    <div id="formatEducation" class="searchItem ifLearned" title="Укажите форму обучения">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Очно / Заочно: </span>
                                        <div class="searchItemBody">
                                            <select name="ochnoZaochno" class="ifLearned">
                                                <option value="0" selected></option>
                                                <option value="1">Очно</option>
                                                <option value="2">Заочно</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="yearOfEnd" class="searchItem ifLearned" title="Укажите год окончания учебного заведения">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Год окончания: </span>
                                        <div class="searchItemBody">
                                            <input class="ifLearned" type="text" size="9">
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="edited private">
                                    <legend>
                                        Работа
                                    </legend>
                                    <div>
                                        <input type="checkbox" name="notWorkCheckbox" id="notWorkCheckbox">
                                        Я не работаю
                                    </div>
                                    <div class="searchItem ifWorked">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Место работы: </span>
                                        <div class="searchItemBody">
                                            <input class="ifWorked" type="text" size="30">
                                        </div>
                                    </div>
                                    <div class="searchItem ifWorked">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Должность: </span>
                                        <div class="searchItemBody">
                                            <input class="ifWorked" type="text" size="33">
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="edited private">
                                    <legend>
                                        Коротко о себе
                                    </legend>
                                    <div class="searchItem">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">В каком регионе родились: </span>
                                        <div class="searchItemBody">
                                            <input type="text" size="42">
                                        </div>
                                    </div>
                                    <div class="searchItem">
                                        <div class="required">
                                            *
                                        </div>
                                        <span class="searchItemLabel">Родной город, населенный пункт: </span>
                                        <div class="searchItemBody">
                                            <input type="text" size="36">
                                        </div>
                                    </div>
                                    <div class="searchItem">
                                        <div class="required"></div>
                                        <span class="searchItemLabel">Коротко о себе и своих интересах: </span>
                                    </div>
                                    <div class="searchItem">
                                        <div class="required"></div>
                                            <textarea name="shortlyAboutMe" cols="71" rows="4"></textarea>
                                    </div>
                                </fieldset>
                                <div class="shadowText" style="margin-top: 7px;">
                                    По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
                                </div>
                            </div>
                            <div id="tabs-3">
                                <div class="shadowText">
                                    Укажите, пожалуйста, адрес Вашей личной страницы минимум в одной социальной сети. Это позволит системе представить Вас собственникам (только тех объектов, которыми Вы сами заинтересуетесь).
                                </div>
                                <fieldset class="edited private">
                                    <legend>
                                        Страницы в социальных сетях
                                    </legend>
                                    <div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                                        <div class="required"></div>
                                        <img src="img/vkontakte.jpg">
                                        <div class="searchItemBody">
                                            <input type="text" name="vkontakte" size="62" placeholder="http://vk.com/...">
                                        </div>
                                    </div>
                                    <div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                                        <div class="required"></div>
                                        <img src="img/odnoklassniki.png">
                                        <div class="searchItemBody">
                                            <input type="text" name="odnoklassniki" size="68" placeholder="http://www.odnoklassniki.ru/profile/...">
                                        </div>
                                    </div>
                                    <div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                                        <div class="required"></div>
                                        <img src="img/facebook.jpg">
                                        <div class="searchItemBody">
                                            <input type="text" name="facebook" size="71" placeholder="https://www.facebook.com/profile.php?...">
                                        </div>
                                    </div>
                                    <div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
                                        <div class="required"></div>
                                        <img src="img/twitter.png">
                                        <div class="searchItemBody">
                                            <input type="text" name="twitter" size="62" placeholder="https://twitter.com/...">
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="shadowText" style="margin-top: 7px;">
                                    По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
                                </div>
                            </div>
							<div id="tabs-4">
								<div class="shadowText">
									Заполните форму как можно подробнее, это позволит системе подобрать для Вас наиболее интересные предложения
								</div>
								<div id="extendedSearchParametersBlock">
									<div id="leftBlockOfSearchParameters" style="display: inline-block;">
										<fieldset class="edited">
											<legend>
												Характеристика объекта
											</legend>
											<div class="searchItem">
												<span class="searchItemLabel"> Тип: </span>
												<div class="searchItemBody">
													<select name="typeOfObject">
														<option value="flat" selected>квартира</option>
														<option value="room">комната</option>
														<option value="house">дом, коттедж</option>
														<option value="townhouse">таунхаус</option>
														<option value="dacha">дача</option>
														<option value="garage">гараж</option>
													</select>
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Количество комнат: </span>
												<div class="searchItemBody">
													<input type="checkbox" value="1" name="amountOfRooms">
													1
													<input type="checkbox" value="2" name="amountOfRooms">
													2
													<input type="checkbox" value="3" name="amountOfRooms">
													3
													<input type="checkbox" value="4" name="amountOfRooms">
													4
													<input type="checkbox" value="5" name="amountOfRooms">
													5
													<input type="checkbox" value="6" name="amountOfRooms">
													6...
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Комнаты смежные: </span>
												<div class="searchItemBody">
													<select name="adjacentRooms">
														<option value="1" selected>не имеет значения</option>
														<option value="2">только изолированные</option>
													</select>
												</div>
											</div>
											<div class="searchItem">
												<span class="searchItemLabel"> Этаж: </span>
												<div class="searchItemBody">
													<select name="floor">
														<option value="1" selected>любой</option>
														<option value="2">не первый</option>
														<option value="3">не первый и не последний</option>
													</select>
												</div>
											</div>
											<div>
												<input type="checkbox">
												С мебелью и бытовой техникой
											</div>
										</fieldset>
										<fieldset class="edited">
											<legend>
												Стоимость
											</legend>
											<div class="searchItem">
												<div class="searchItemLabel">
													Арендная плата (в месяц с учетом к.у.)
												</div>
												<div class="searchItemBody">
													от
													<input type="text" name="minCost" size="10">
													руб., до
													<input type="text" name="maxCost" size="10">
													руб.
												</div>
											</div>
											<div class="searchItem" title="Какую сумму Вы готовы передать собственнику в качестве возвращаемого гарантийного депозита, а также предоплаты за проживание, кроме арендной платы за первый месяц">
												<span class="searchItemLabel"> Залог </span>
												<div class="searchItemBody">
													до
													<input type="text" name="maxCost" size="10">
													руб.
												</div>
											</div>
										</fieldset>
									</div>
									<div id="rightBlockOfSearchParameters">
										<fieldset>
											<legend>
												Район
											</legend>
											<div class="searchItem">
												<div class="searchItemBody">
													<ul>
														<li>
															<input type="checkbox" name="district" value="1">
															Автовокзал (южный)
														</li>
														<li>
															<input type="checkbox" name="district" value="2">
															Академический
														</li>
														<li>
															<input type="checkbox" name="district" value="3">
															Ботанический
														</li>
														<li>
															<input type="checkbox" name="district" value="4">
															ВИЗ
														</li>
														<li>
															<input type="checkbox" name="district" value="5">
															Вокзальный
														</li>
														<li>
															<input type="checkbox" name="district" value="6">
															Втузгородок
														</li>
														<li>
															<input type="checkbox" name="district" value="7">
															Горный щит
														</li>
														<li>
															<input type="checkbox" name="district" value="8">
															Елизавет
														</li>
														<li>
															<input type="checkbox" name="district" value="9">
															ЖБИ
														</li>
														<li>
															<input type="checkbox" name="district" value="10">
															Завокзальный
														</li>
														<li>
															<input type="checkbox" name="district" value="11">
															Заречный
														</li>
														<li>
															<input type="checkbox" name="district" value="12">
															Изоплит
														</li>
														<li>
															<input type="checkbox" name="district" value="13">
															Исток
														</li>
														<li>
															<input type="checkbox" name="district" value="14">
															Калиновский
														</li>
														<li>
															<input type="checkbox" name="district" value="15">
															Кольцово
														</li>
														<li>
															<input type="checkbox" name="district" value="16">
															Компрессорный
														</li>
														<li>
															<input type="checkbox" name="district" value="17">
															Лечебный
														</li>
														<li>
															<input type="checkbox" name="district" value="18">
															Медный
														</li>
														<li>
															<input type="checkbox" name="district" value="19">
															Нижнеисетский
														</li>
														<li>
															<input type="checkbox" name="district" value="20">
															Парковый
														</li>
														<li>
															<input type="checkbox" name="district" value="21">
															Пионерский
														</li>
														<li>
															<input type="checkbox" name="district" value="22">
															Птицефабрика
														</li>
														<li>
															<input type="checkbox" name="district" value="23">
															Семь ключей
														</li>
														<li>
															<input type="checkbox" name="district" value="24">
															Сибирский тракт
														</li>
														<li>
															<input type="checkbox" name="district" value="25">
															Синие камни
														</li>
														<li>
															<input type="checkbox" name="district" value="26">
															Совхозный
														</li>
														<li>
															<input type="checkbox" name="district" value="27">
															Сортировка новая
														</li>
														<li>
															<input type="checkbox" name="district" value="28">
															Сортировка старая
														</li>
														<li>
															<input type="checkbox" name="district" value="29">
															Уктус
														</li>
														<li>
															<input type="checkbox" name="district" value="30">
															УНЦ
														</li>
														<li>
															<input type="checkbox" name="district" value="31">
															Уралмаш
														</li>
														<li>
															<input type="checkbox" name="district" value="32">
															Химмаш
														</li>
														<li>
															<input type="checkbox" name="district" value="33">
															Центр
														</li>
														<li>
															<input type="checkbox" name="district" value="34">
															Чермет
														</li>
														<li>
															<input type="checkbox" name="district" value="35">
															Шарташ
														</li>
														<li>
															<input type="checkbox" name="district" value="36">
															Широкая речка
														</li>
														<li>
															<input type="checkbox" name="district" value="37">
															Эльмаш
														</li>
														<li>
															<input type="checkbox" name="district" value="38">
															Юго-запад
														</li>
														<li>
															<input type="checkbox" name="district" value="39">
															За городом
														</li>
													</ul>
												</div>
											</div>
										</fieldset>
									</div>
									<!-- /end.rightBlockOfSearchParameters -->

									<fieldset class="edited private">
										<legend>
											Особые параметры поиска
										</legend>
										<div class="searchItem">
											<span class="searchItemLabel">Как собираетесь проживать: </span>
											<div class="searchItemBody">
												<select name="withWho" id="withWho">
													<option value="1" selected>один</option>
													<option value="2">семейная пара</option>
													<option value="3">несемейная пара</option>
													<option value="4">со знакомыми</option>
												</select>
											</div>
										</div>
										<div class="searchItem" id="withWhoDescription" style="display: none;">
											<div class="searchItemLabel">
												Ссылки на страницы сожителей:
											</div>
											<div class="searchItemBody">
												<textarea name="liksToFriends" cols="40" rows="3"></textarea>
											</div>
										</div>
										<div class="searchItem">
											<span class="searchItemLabel">Дети: </span>
											<div class="searchItemBody">
												<select name="children" id="children">
													<option value="0" selected>без детей</option>
													<option value="1">с детьми младше 4-х лет</option>
													<option value="2">с детьми старше 4-х лет</option>
												</select>
											</div>
										</div>
										<div class="searchItem" id="childrenDescription" style="display: none;">
											<div class="searchItemLabel">
												Сколько у Вас детей и какого возраста:
											</div>
											<div class="searchItemBody">
												<textarea name="howManyChildren" cols="40" rows="3"></textarea>
											</div>
										</div>
										<div class="searchItem">
											<span class="searchItemLabel">Животные: </span>
											<div class="searchItemBody">
												<select name="animals" id="animals">
													<option value="0" selected>без животных</option>
													<option value="1">с животным(ми)</option>
												</select>
											</div>
										</div>
										<div class="searchItem" id="animalsDescription" style="display: none;">
											<div class="searchItemLabel">
												Сколько у Вас животных и какого вида:
											</div>
											<div class="searchItemBody">
												<textarea name="howManyAnimals" cols="40" rows="3"></textarea>
											</div>
										</div>
										<div class="searchItem">
											<span class="searchItemLabel">Ориентировочный срок аренды:</span>
											<div class="searchItemBody">
												<input type="text" name="period" size="20">
											</div>
										</div>
										<div class="searchItem">
											<div class="searchItemLabel">
												Дополнительные условия поиска:
											</div>
											<div class="searchItemBody">
												<textarea name="additionalDescriptionOfSearch" cols="50" rows="4"></textarea>
											</div>
										</div>
									</fieldset>
								</div>
								<div class="shadowText" style="margin-top: 7px;">
									По окончании заполнения полей на всех вкладках введите текст капчи и нажмите кнопку "Готово" справа внизу
								</div>
							</div><!-- /end.tabs-2 -->
						</div><!-- /end.tabs -->
						<div class="readyButton">
							<button type="submit" name="readyButton" id="readyButton">
								Готово
							</button>
						</div>
						<div class="capcha">
							<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=6LfPj9QSAAAAADiTQL68cyA1TlIBZMq5wHe6n_TK"></script>
							<noscript>
								<iframe src="http://www.google.com/recaptcha/api/noscript?k=your_public_key" height="300" width="500" frameborder="0"></iframe>
								<br>
								<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
								<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
							</noscript>
						</div>
					</form>
				</div><!-- /end.wrapperOfTabs-->
			</div><!-- /end.page_main_content -->
			<!-- Блок для прижатия подвала к низу страницы без закрытия части контента, его CSS высота доллжна быть = высоте футера -->
			<div class="page-buffer"></div>
		</div><!-- /end.page_without_footer -->
		<div class="footer">
			2012 «Хани Хом», вопросы и пожелания по работе портала можно передавать по телефону 8-922-143-16-15
		</div><!-- /end.footer -->

		<!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->

		<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

		<!-- jQuery UI с моей темой оформления -->
		<script src="js/vendor/jquery-ui-1.8.22.custom.min.js"></script>
        <script src="js/vendor/jquery.ui.datepicker-ru.js"></script>

        <script src="js/vendor/fileuploader.js" type="text/javascript"></script>

		<!-- scripts concatenated and minified via build script -->
		<script src="js/main.js"></script>
		<script src="js/registrationForm.js"></script>

		<!-- end scripts -->

		<!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
		mathiasbynens.be/notes/async-analytics-snippet -->
		<!-- <script>
		var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
		g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
		s.parentNode.insertBefore(g,s)}(document,'script'));
		</script> -->
	</body>
</html>
