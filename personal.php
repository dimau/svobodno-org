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
		<meta charset="utf-8">

		<!-- Use the .htaccess and remove these lines to avoid edge case issues.
		More info: h5bp.com/i/378 -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Личный кабинет</title>
		<meta name="description" content="Личный кабинет пользователя">

		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="initialscale=1.0, width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

		<link rel="stylesheet" href="css/jquery-ui-1.8.22.custom.css">
		<link rel="stylesheet" href="css/main.css">
		<style>
			/* Стили для создания нового Объявления*/
			.actionChangeStatusAdvert {
				float: right;
				margin-top: -0.1em;
				margin-right: 4px;
				font-weight: normal;
				font-size: 0.8em;
			}

			#addressForm {
				clear: both;
			}

			.inputItem, .objectDescriptionItem {
				width: 100%;
				margin-top: 7px;
				margin-bottom: 7px;
			}

			.inputItem .label, .objectDescriptionItem .objectDescriptionItemLabel {
				min-width: 150px;
				width: 49%;
				text-align: right;
				display: inline-block;
				vertical-align: top;
			}

			.objectDescriptionItem .objectDescriptionBody {
				display: inline-block;
				width: 49%;
			}

			.objectDescriptionBody.furniture {
				min-width: 300px;
			}

			table td {
				vertical-align: middle;
				padding: 5px;
			}

			#newAdvertButton {
				margin-bottom: 10px;
			}

			.advertHeader {
				border: 1px solid #AAAAAA;
				padding: 5px;
				background-color: #B5B5B5;
				font-size: 1.2em;
				border-radius: 5px 5px 0 0;
			}

			.advertHeaderStatus {
				color: red;
				display: inline-block;
			}

			.advertDescriptionEdit {
				border: 1px solid #AAAAAA;
				border-radius: 0 0 5px 5px;
				padding: 10px;
			}

			.advertDescriptionChapterHeader {
				background-color: #8e4a15;
				font-size: 1.2em;
				padding-left: 5px;
				border-radius: 5px;
				color: white;
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
					<div class="headerOfPage">
						Личный кабинет
					</div>
					<div id="tabs">
						<ul>
							<li>
								<a href="#tabs-1">Профиль</a>
							</li>
							<li>
								<a href="#tabs-2">Новости (<span id="amountUnreadNews">12</span>)</a>
							</li>
							<li>
								<a href="#tabs-3">Мои объявления</a>
							</li>
							<li>
								<a href="#tabs-4">Поиск</a>
							</li>
							<li>
								<a href="#tabs-5">Избранное</a>
							</li>
						</ul>
						<div id="tabs-1">
							<div id="notEditingProfileParametersBlock">
								<div class="setOfInstructions">
									<a href="#">редактировать</a>
									<br>
								</div>
								<div class="fotosWrapper">
									<div class="bigFotoWrapper">
										<img class="bigFoto">
									</div>
								</div>
								<div class="profileInformation">
									<ul class="listDescription">
										<li>
											<span class="FIO">Ушаков Дмитрий Владимирович</span>
										</li>
										<li>
											<br>
										</li>
										<li>
											<span class="headOfString">Образование:</span> УГТУ-УПИ, инженер автоматики и управления в информационных системах, закончил в 2009 г. причем с отличием
										</li>
										<li>
											<span class="headOfString">Работа:</span> СКБ Контур, менеджер проектов
										</li>
										<li>
											<span class="headOfString">Национальность:</span> русский
										</li>
										<li>
											<span class="headOfString">Пол:</span> мужской
										</li>
										<li>
											<span class="headOfString">День рождения:</span> 27.01.1987
										</li>
										<li>
											<span class="headOfString">Возраст:</span> 25
										</li>
										<li>
											<br>
										</li>
										<li>
											<span style="font-weight: bold;">Контакты:</span>
										</li>
										<li>
											<span class="headOfString">e-mail:</span> dimau777@gmail.com
										</li>
										<li>
											<span class="headOfString">Телефон:</span> 89221431615
										</li>
										<li>
											<br>
										</li>
										<li>
											<span style="font-weight: bold;">Малая Родина:</span>
										</li>
										<li>
											<span class="headOfString">Город (населенный пункт):</span> Лысьва
										</li>
										<li>
											<span class="headOfString">Регион:</span> Пермский край
										</li>
										<li>
											<br>
										</li>
										<li>
											<span style="font-weight: bold;">Коротко о себе и своих интересах:</span>
										</li>
										<li>
											Я немного замкнутый перфекционист и вообще неадекватный человек, возьмите меня замуж или в жены, ха-ха-ха
										</li>
										<li>
											<br>
										</li>
										<li>
											<span style="font-weight: bold;">Страницы в социальных сетях:</span>
										</li>
										<li>
											<ul class="linksToAccounts">
												<li>
													<a href="http://vk.com/ushakovd">http://vk.com/ushakovd</a>
												</li>
												<li>
													<a href="http://vk.com/ushakovd">http://vk.com/ushakovd</a>
												</li>
												<li>
													<a href="http://vk.com/ushakovd">http://vk.com/ushakovd</a>
												</li>
											</ul>
										</li>
									</ul>
								</div>
							</div>

							<form name="profileParameters" id="editingProfileParametersBlock" class="descriptionFieldsetsWrapper" style="display: none;">
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
											<input type="text" size="23">
										</div>
									</div>
									<div class="searchItem">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Отчество: </span>
										<div class="searchItemBody">
											<input type="text" size="23">
										</div>
									</div>
									<div class="searchItem">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Фамилия: </span>
										<div class="searchItemBody">
											<input type="text" size="23">
										</div>
									</div>
									<div class="searchItem">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Пол: </span>
										<div class="searchItemBody">
											<select name="sex">
												<option value="0" selected></option>
												<option value="man">мужской</option>
												<option value="woman">женский</option>
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
												<option value="0" selected></option>
												<option value="1">русский</option>
												<option value="2">европеец, американец</option>
												<option value="3">СНГ, восточная нац-сть</option>
											</select>
										</div>
									</div>
									<div class="searchItem">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">День рождения: </span>
										<div class="searchItemBody">
											<input type="text" size="15">
										</div>
									</div>
								</fieldset>

								<div style="display: inline-block; vertical-align: top;">
									<fieldset class="edited private" style="display: block;">
										<legend>
											Логин и пароль
										</legend>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Логин: </span>
											<div class="searchItemBody">
												<input type="text" size="20">
											</div>
										</div>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">Пароль: </span>
											<div class="searchItemBody">
												<input type="password" size="20">
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
												<input type="text" size="15">
											</div>
										</div>
										<div class="searchItem">
											<div class="required">
												*
											</div>
											<span class="searchItemLabel">e-mail: </span>
											<div class="searchItemBody">
												<input type="text" size="15">
											</div>
										</div>
									</fieldset>
								</div>

								<fieldset class="edited private">
									<legend>
										Страницы в социальных сетях
									</legend>
									<div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
										<div class="required"></div>
										<select name="selectSocialNetwork1">
											<option value="0" selected></option>
											<option value="1">В контакте</option><option value="2">Одноклассники</option><option value="3">Facebook</option><option value="4">Twitter</option><option value="5">Мой круг</option><option value="6">Google+</option>
										</select>
										<div class="searchItemBody">
											<input type="text" name="socialNetwork1" size="30" value="http://">
										</div>
									</div>
									<div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
										<div class="required"></div>
										<select name="selectSocialNetwork2">
											<option value="0" selected></option>
											<option value="1">В контакте</option><option value="2">Одноклассники</option><option value="3">Facebook</option><option value="4">Twitter</option><option value="5">Мой круг</option><option value="6">Google+</option>
										</select>
										<div class="searchItemBody">
											<input type="text" name="socialNetwork2" size="30" value="http://">
										</div>
									</div>
									<div class="searchItem" title="Скопируйте ссылку из адресной строки браузера при просмотре своей личной страницы в социальной сети">
										<div class="required"></div>
										<select name="selectSocialNetwork3">
											<option value="0" selected></option>
											<option value="1">В контакте</option><option value="2">Одноклассники</option><option value="3">Facebook</option><option value="4">Twitter</option><option value="5">Мой круг</option><option value="6">Google+</option>
										</select>
										<div class="searchItemBody">
											<input type="text" name="socialNetwork3" size="30" value="http://">
										</div>
									</div>
								</fieldset>

								<fieldset class="edited private">
									<legend>
										Образование
									</legend>
									<div>
										<input type="checkbox" name="notLearnCheckbox" id="notLearnCheckbox">
										Я нигде не учился
									</div>
									<div class="searchItem ifLearned" title="Укажите последнее учебное заведение, если Вы заканчивали несколько">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Учебное заведение: </span>
										<div class="searchItemBody">
											<input class="ifLearned" type="text" size="30">
										</div>
									</div>
									<div class="searchItem ifLearned">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Специальность: </span>
										<div class="searchItemBody">
											<input class="ifLearned" type="text" size="30">
										</div>
									</div>
									<div class="searchItem ifLearned" title="Укажите курс, на котором учитесь или год окончания, если Вы уже закончили учебное заведение">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Текущий статус: </span>
										<div class="searchItemBody">
											<input class="ifLearned" type="text" size="30">
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
											<input class="ifWorked" type="text" size="30">
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
											<input type="text" size="20">
										</div>
									</div>
									<div class="searchItem">
										<div class="required">
											*
										</div>
										<span class="searchItemLabel">Родной город, населенный пункт: </span>
										<div class="searchItemBody">
											<input type="text" size="20">
										</div>
									</div>
									<div class="searchItem">
										<div class="required"></div>
										<span class="searchItemLabel">Коротко о себе и своих интересах: </span>
										<div class="searchItemBody">
											<textarea name="shortlyAboutMe" cols="70" rows="4"></textarea>
										</div>
									</div>
								</fieldset>

								<!--

								Кроме того, для собственников не нужно передавать блоки Образование и Работа, Коротко о себе
								Также для собственника не формируется вкладка Условия поиска
								Фото становится необязательным - убрать звездочку

								Сделать проверку перед отправкой и серверную часть капчи

								Но собственнику отправляется дополнительно

								-->

								<fieldset class="edited private" style="min-width: 300px; min-height: 200px;">
									<legend title="Для успешной регистрации должна быть загружена хотя бы 1 фотография">
										<div class="required">
											*
										</div>
										Фотографии
									</legend>
									<button>
										Загрузить фотографии
									</button>
								</fieldset>
								<div class="clearBoth"></div>
								<input type="submit" value="Сохранить" id="saveProfileParameters" class="bottomButton button">
								<div class="clearBoth"></div>
							</form><!-- /end.descriptionFieldsetsWrapper -->
							<div class="clearBoth"></div>
						</div><!-- /end.tabs-1 -->
						<div id="tabs-2">
							<div class="shadowText">
								На этой вкладке располагается информация о важных событиях, случившихся на ресурсе Хани Хом, как например: появление новых потенциальных арендаторов, заинтересовавшихся Вашим объявлением, или новых объявлений, которые подходят под Ваш запрос
							</div>
							<div class="news unread">
								<div class="newsHeader">
									Претендент на квартиру по адресу: улица Сибирский тракт 50 летия 107, кв 70.
									<div class="actionReaded">
										<a href="#">прочитал</a>
									</div>
									<div class="clearBoth"></div>
								</div>

								<div class="fotosWrapper">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div>
								<ul class="setOfInstructions">
									<li>
										<a href="#">подробнее</a>
									</li>
								</ul>
								<ul class="listDescription">
									<li>
										<span class="headOfString">ФИО:</span>
										Ушаков Дмитрий Владимирович
									</li>
									<li>
										<span class="headOfString">Возраст:</span>
										25
									</li>
									<li>
										<span class="headOfString">Срок аренды:</span>
										долгосрочно
									</li>
									<li>
										<span class="headOfString">С кем жить:</span>
										несемейная пара
									</li>
									<li>
										<span class="headOfString">Дети:</span>
										нет
									</li>
									<li>
										<span class="headOfString">Животные:</span>
										нет
									</li>
									<li>
										<span class="headOfString">Телефон:</span>
										89221431615
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
							<div class="news unread">
								<div class="newsHeader">
									Изменение статуса объявления
									<div class="actionReaded">
										<a href="#">прочитал</a>
									</div>
									<div class="clearBoth"></div>
								</div>
								<div class="fotosWrapper">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div>
								<ul class="setOfInstructions">
									<li>
										<a href="#">подробнее</a>
									</li>
								</ul>
								<ul class="listDescription">
									<li>
										<span class="headOfString">Адрес объекта:</span>
										улица Шаумяна 107, кв 70
									</li>
									<li>
										<span class="headOfString">Статус изменен на:</span>
										<span style="color: green">объявление опубликовано</span>
									</li>
									<li>
										<span class="headOfString">Дата:</span>
										25.09.2012
									</li>
									<li>
										<span class="headOfString">Комментарий к статусу:</span>
										объявление опубликовано на ресурсе Хани Хом, а также поставлено в очередь на автоматическую ежедневную публикацию на основных интернет-порталах города. Это обеспечит максимальный приток арендаторов, из которых Вы сможете выбрать наиболее ответственных и надежных
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
							<div class="news">
								<div class="newsHeader">
									Претендент на квартиру по адресу: улица Сибирский тракт 50 летия 107, кв 70.
								</div>
								<div class="fotosWrapper">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div>
								<ul class="setOfInstructions">
									<li>
										<a href="#">подробнее</a>
									</li>
								</ul>
								<ul class="listDescription">
									<li>
										<span class="headOfString">ФИО:</span>
										Ушаков Дмитрий Владимирович
									</li>
									<li>
										<span class="headOfString">Возраст:</span>
										25
									</li>
									<li>
										<span class="headOfString">Срок аренды:</span>
										долгосрочно
									</li>
									<li>
										<span class="headOfString">С кем жить:</span>
										несемейная пара
									</li>
									<li>
										<span class="headOfString">Дети:</span>
										нет
									</li>
									<li>
										<span class="headOfString">Животные:</span>
										нет
									</li>
									<li>
										<span class="headOfString">Телефон:</span>
										89221431615
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
							<div class="news">
								<div class="newsHeader">
									Новое предложение по Вашему поиску
								</div>
								<div class="fotosWrapper">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div>
								<ul class="setOfInstructions">
									<li>
										<a href="#">подробнее</a>
									</li>
									<li>
										<a href="#">посмотреть на карте</a>
									</li>
								</ul>
								<ul class="listDescription">
									<li>
										<span class="headOfString">Тип:</span> Квартира
									</li>
									<li>
										<span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
									</li>
									<li>
										<span class="headOfString">Единовременная комиссия:</span>
										<a href="#"> 3000 руб. (40%) собственнику</a>
									</li>
									<li>
										<span class="headOfString">Адрес:</span>
										улица Посадская 51
									</li>
									<li>
										<span class="headOfString">Количество комнат:</span>
										2, смежные
									</li>
									<li>
										<span class="headOfString">Площадь (жилая/общая):</span>
										22.4/34 м²
									</li>
									<li>
										<span class="headOfString">Этаж:</span>
										3 из 10
									</li>
									<li>
										<span class="headOfString">Срок сдачи:</span>
										долгосрочно
									</li>
									<li>
										<span class="headOfString">Мебель:</span>
										есть
									</li>
									<li>
										<span class="headOfString">Район:</span>
										Центр
									</li>
									<li>
										<span class="headOfString">Телефон собственника:</span>
										<a href="#">показать</a>
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
						</div>
						<div id="tabs-3">
							<button id="newAdvertButton">
								Новое объявление
							</button>
							<div id="modalWindowNewAdvert">
								<form name="advert0" class="advertDescriptionEdit">
									<div class="advertDescriptionChapter">
										<div class="advertDescriptionChapterHeader">
											Описание объекта
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Тип объекта:
											</div>
											<div class="objectDescriptionBody">
												<select name="typeOfObject">
													<option value="0" selected></option>
													<option value="flat">квартира</option>
													<option value="room">комната</option>
													<option value="house">дом, коттедж</option>
													<option value="townhouse">таунхаус</option>
													<option value="dacha">дача</option>
													<option value="garage">гараж</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												С какого числа можно въезжать:
											</div>
											<div class="objectDescriptionBody">
												Выбор даты
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												На какой срок сдается:
											</div>
											<div class="objectDescriptionBody">
												<input type="text" name="termOfLease" value="">
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Количество комнат в квартире, доме:
											</div>
											<div class="objectDescriptionBody">
												<select name="amountOfRooms">
													<option value="0" selected></option>
													<option value="1">1</option>
													<option value="2">2</option>
													<option value="3">3</option>
													<option value="4">4</option>
													<option value="5">5</option>
													<option value="6">6 и более</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Комнаты смежные:
											</div>
											<div class="objectDescriptionBody">
												<select name="adjacentRooms">
													<option value="0" selected></option>
													<option value="1">нет</option>
													<option value="2">да</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Санузел:
											</div>
											<div class="objectDescriptionBody">
												<select name="typeOfBathrooms">
													<option value="0" selected></option>
													<option value="1">раздельный</option>
													<option value="2">совмещенный</option>
													<option value="3">2</option>
													<option value="4">3</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Балкон/лоджия:
											</div>
											<div class="objectDescriptionBody">
												<select name="typeOfBalcony">
													<option value="0" selected></option>
													<option value="1">нет</option>
													<option value="2">балкон</option>
													<option value="3">лоджия</option>
													<option value="4">балкон и лоджия</option>
													<option value="5">балкон и эркер</option>
													<option value="6">2 балкона и более</option>
													<option value="7">2 лоджии и более</option>
													<option value="8">2 эркера и более</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Остекление балкона/лоджии:
											</div>
											<div class="objectDescriptionBody">
												<select name="glazed">
													<option value="0" selected></option>
													<option value="1">нет</option>
													<option value="2">да</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Общая площадь:
											</div>
											<div class="objectDescriptionBody">
												<input type="text" size="7" name="totalАrea" value="">
												м²
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Жилая площадь:
											</div>
											<div class="objectDescriptionBody">
												<input type="text" size="7" name="livingSpace" value="">
												м²
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Этаж:
											</div>
											<div class="objectDescriptionBody">
												<input type="text" size="3" name="floor" value="">
												из
												<input type="text" size="3" name="totalAmountFloor" value="">
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Консьерж:
											</div>
											<div class="objectDescriptionBody">
												<select name="concierge">
													<option value="0" selected></option>
													<option value="1">есть</option>
													<option value="2">нет</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Домофон:
											</div>
											<div class="objectDescriptionBody">
												<select name="intercom">
													<option value="0" selected></option>
													<option value="1">есть и работает</option>
													<option value="2">есть, но не работает</option>
													<option value="3">нет</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Парковка:
											</div>
											<div class="objectDescriptionBody">
												<select name="parking">
													<option value="0" selected></option>
													<option value="1">стихийная</option>
													<option value="2">охраняемая</option>
													<option value="3">неохраняемая</option>
													<option value="4">подземная</option>
													<option value="5">отсутствует</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Фотографии (вид из окна, двор и дом, каждая из комнат, ванна, туалет, кухня)
											</div>
											<div class="objectDescriptionBody"></div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="addressChapter">
										<div class="advertDescriptionChapterHeader">
											Местоположение
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Город:
											</div>
											<div class="objectDescriptionBody">
												<span> Екатеринбург</span>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Район:
											</div>
											<div class="objectDescriptionBody">
												<select name="district">
													<option value="0" selected></option>
													<option value="1">Автовокзал (южный)</option>
													<option value="2">Академический</option>
													<option value="3">Ботанический</option>
													<option value="4">ВИЗ</option>
													<option value="5">Вокзальный</option>
													<option value="6">Втузгородок</option>
													<option value="7">Горный щит</option>
													<option value="8">Елизавет</option>
													<option value="9">ЖБИ</option>
													<option value="10">Завокзальный</option>
													<option value="11">Заречный</option>
													<option value="12">Изоплит</option>
													<option value="13">Исток</option>
													<option value="14">Калиновский</option>
													<option value="15">Кольцово</option>
													<option value="16">Компрессорный</option>
													<option value="17">Лечебный</option>
													<option value="18">Медный</option>
													<option value="19">Нижнеисетский</option>
													<option value="20">Парковый</option>
													<option value="21">Пионерский</option>
													<option value="22">Птицефабрика</option>
													<option value="23">Семь ключей</option>
													<option value="24">Сибирский тракт</option>
													<option value="25">Синие камни</option>
													<option value="26">Совхозный</option>
													<option value="27">Сортировка новая</option>
													<option value="28">Сортировка старая</option>
													<option value="29">Уктус</option>
													<option value="30">УНЦ</option>
													<option value="31">Уралмаш</option>
													<option value="32">Химмаш</option>
													<option value="33">Центр</option>
													<option value="34">Чермет</option>
													<option value="35">Шарташ</option>
													<option value="36">Широкая речка</option>
													<option value="37">Эльмаш</option>
													<option value="38">Юго-запад</option>
													<option value="39">За городом</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Улица и номер дома:
											</div>
											<div class="objectDescriptionBody" style="min-width: 400px">
												<table>
													<tbody>
														<tr>
															<td>
															<input type="text" name="address" id="addressTextBox" size="30" value="">
															<input type="button" value="Проверить адрес" id="checkAddressButton">
															</td>
														</tr>
														<tr>
															<td><!-- Карта Яндекса --><div id="mapForNewAdvert" style="width: 400px; height: 400px; margin-top: 8px;"></div></td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Номер квартиры:
											</div>
											<div class="objectDescriptionBody">
												<input type="text" name="apartment number" size="7" value="">
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Станция метро рядом:
											</div>
											<div class="objectDescriptionBody">
												<select name="subwayStation">
													<option value="0" selected></option>
													<option value="10">Нет</option>
													<option value="1">Проспект Космонавтов</option>
													<option value="2">Уралмаш</option>
													<option value="3">Машиностроителей</option>
													<option value="4">Уральская</option>
													<option value="5">Динамо</option>
													<option value="6">Площадь 1905 г.</option>
													<option value="7">Геологическая</option>
													<option value="8">Чкаловская</option>
													<option value="9">Ботаническая</option>
												</select>
												<input type="text" name="distanceToMetroStation" size="4" value="">
												мин.ходьбы
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="costChapter">
										<div class="advertDescriptionChapterHeader">
											Стоимость, условия оплаты
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Плата за аренду:
											</div>
											<div class="objectDescriptionBody">
												<input type="text" name="costOfRenting" size="7" value="">
												руб. в месяц
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Коммунальные услуги оплачиваются арендатором дополнительно:
											</div>
											<div class="objectDescriptionBody" style="min-width: 400px">
												<select name="utilities">
													<option value="0" selected></option>
													<option value="1">да</option>
													<option value="2">нет</option>
												</select>
												Летом
												<input type="text" name="costInSummer" size="7" value="">
												руб. Зимой
												<input type="text" name="costInWinter" size="7" value="">
												руб.
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Электроэнергия оплачивается дополнительно:
											</div>
											<div class="objectDescriptionBody">
												<select name="electricPower">
													<option value="0" selected></option>
													<option value="1">да</option>
													<option value="2">нет</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Залог:
											</div>
											<div class="objectDescriptionBody">
												<select name="bail">
													<option value="0" selected></option>
													<option value="1">есть</option>
													<option value="2">нет</option>
												</select>
												<input type="text" name="bailCost" size="7" value="">
												руб.
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="currentStatus">
										<div class="advertDescriptionChapterHeader">
											Текущее состояние
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Ремонт:
											</div>
											<div class="objectDescriptionBody">
												<select name="repair">
													<option value="0" selected></option>
													<option value="1">не выполнялся (новый дом)</option>
													<option value="2">больше года назад</option>
													<option value="3">меньше 1 года назад</option>
													<option value="4">сделан только что</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Отделка (жилых помещений):
											</div>
											<div class="objectDescriptionBody" style="min-width: 400px">
												<select name="furnish">
													<option value="0" selected></option>
													<option value="1">евростандарт</option>
													<option value="2">косметическая (новые обои, побелка потолков)</option>
													<option value="3">иное</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Окна:
											</div>
											<div class="objectDescriptionBody">
												<select name="windows">
													<option value="0" selected></option>
													<option value="1">деревянные</option>
													<option value="2">стеклопакет</option>
													<option value="3">иное</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Санузел, отделка:
											</div>
											<div class="objectDescriptionBody">
												<select name="wс">
													<option value="0" selected></option>
													<option value="1">кафель</option>
													<option value="2">обои</option>
													<option value="3">побелка</option>
													<option value="4">иное</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Половое покрытие в комнатах:
											</div>
											<div class="objectDescriptionBody">
												<select name="flooring">
													<option value="0" selected></option>
													<option value="1">дерево</option>
													<option value="2">паркет</option>
													<option value="3">ламинат</option>
													<option value="4">ковер</option>
													<option value="5">бетон</option>
													<option value="6">линолеум</option>
													<option value="7">иное</option>
												</select>
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="communication">
										<div class="advertDescriptionChapterHeader">
											Связь
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Интернет:
											</div>
											<div class="objectDescriptionBody">
												<select name="internet">
													<option value="0" selected></option>
													<option value="1">не проведен, нельзя провести</option>
													<option value="2">не проведен, можно провести</option>
													<option value="3">проведен, можно использовать</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Телефон:
											</div>
											<div class="objectDescriptionBody">
												<select name="telephoneLine">
													<option value="0" selected></option>
													<option value="1">не проведен, нельзя провести</option>
													<option value="2">не проведен, можно провести</option>
													<option value="3">проведен, можно использовать</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Кабельное ТВ:
											</div>
											<div class="objectDescriptionBody">
												<select name="cableTV">
													<option value="0" selected></option>
													<option value="1">не проведен, нельзя провести</option>
													<option value="2">не проведен, можно провести</option>
													<option value="3">проведен, можно использовать</option>
												</select>
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="furniture">
										<div class="advertDescriptionChapterHeader">
											Мебель и бытовая техника
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Наличие мебели и бытовой техники:
											</div>
											<div class="objectDescriptionBody" style="min-width: 330px">
												<select name="furnitureYesNo">
													<option value="0" selected></option>
													<option value="1">Сдается с мебелью и бытовой техникой</option>
													<option value="2">Сдается без мебели и бытовой техники</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="sofa">
												Диван:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="sofaAmount" size="3" value="">
												состояние
												<select name="sofaCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="chairBed">
												Кресло-кровать:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="chairBedAmount" size="3" value="">
												состояние
												<select name="chairBedCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="chair">
												Кресло:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="chairAmount" size="3" value="">
												состояние
												<select name="chairCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="stool">
												Стул:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="stoolAmount" size="3" value="">
												состояние
												<select name="stoolCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="tabouret">
												Табурет:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="tabouretAmount" size="3" value="">
												состояние
												<select name="tabouretCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="deskTable">
												Стол письменный, компьютерный:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="deskTableAmount" size="3" value="">
												состояние
												<select name="deskTableCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="coffeeTable">
												Стол журнальный:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="coffeeTableAmount" size="3" value="">
												состояние
												<select name="coffeeTableCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="setOfCabinets">
												Стенка:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="setOfCabinetsAmount" size="3" value="">
												состояние
												<select name="setOfCabinetsCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="cabinet">
												Шкаф для одежды:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="cabinetAmount" size="3" value="">
												состояние
												<select name="cabinetCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>

										<br>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="diningTable">
												Стол обеденный:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="diningTableAmount" size="3" value="">
												состояние
												<select name="diningTableCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="kitchenSet">
												Кухонный гарнитур:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="kitchenSetAmount" size="3" value="">
												состояние
												<select name="kitchenSetCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="cupboards">
												Шкаф для посуды:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="cupboardsAmount" size="3" value="">
												состояние
												<select name="cupboardsCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>

										<br>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="televisionSet">
												Телевизор:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="televisionSetAmount" size="3" value="">
												состояние
												<select name="televisionSetCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="refrigerator">
												Холодильник:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="refrigeratorAmount" size="3" value="">
												состояние
												<select name="refrigeratorCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="washingMachine">
												Стиральная машина:
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="washingMachineAmount" size="3" value="">
												состояние
												<select name="washingMachineCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												<input type="checkbox" name="stove">
												Плита (газовая, электрическая):
											</div>
											<div class="objectDescriptionBody furniture">
												количество
												<input type="text" name="stoveAmount" size="3" value="">
												состояние
												<select name="stoveCurrentStatus">
													<option value="0" selected></option>
													<option value="1">новый</option>
													<option value="2">старый</option>
												</select>
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="requirementsForTenant">
										<div class="advertDescriptionChapterHeader">
											Требования к арендатору
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Пол:
											</div>
											<div class="objectDescriptionBody">
												<input type="checkbox" name="sexOfTenant" value="man">
												мужчина
												<br>
												<input type="checkbox" name="sexOfTenant" value="woman">
												женщина
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Отношения между арендаторами:
											</div>
											<div class="objectDescriptionBody">
												<input type="checkbox" name="relations" value="family">
												семейная пара
												<br>
												<input type="checkbox" name="relations" value="notFamily">
												несемейная пара
												<br>
												<input type="checkbox" name="relations" value="alone">
												один человек
												<br>
												<input type="checkbox" name="relations" value="group">
												группа людей
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Национальность:
											</div>
											<div class="objectDescriptionBody">
												<input type="checkbox" name="nationality" value="russian">
												русским
												<br>
												<input type="checkbox" name="nationality" value="european">
												европейцам, американцам
												<br>
												<input type="checkbox" name="nationality" value="east">
												СНГ, восточным национальностям
												<br>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Дети:
											</div>
											<div class="objectDescriptionBody">
												<select name="children">
													<option value="0" selected></option>
													<option value="1">не имеет значения</option>
													<option value="2">с детьми старше 4-х лет</option>
													<option value="3">только без детей</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Животные:
											</div>
											<div class="objectDescriptionBody">
												<select name="animals">
													<option value="0" selected></option>
													<option value="1">не имеет значения</option>
													<option value="2">только без животных</option>
												</select>
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="specialConditions">
										<div class="advertDescriptionChapterHeader">
											Особые условия
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Как часто собственник проверяет сдаваемую недвижимость:
											</div>
											<div class="objectDescriptionBody" style="min-width: 330px">
												<select name="checking">
													<option value="0" selected></option>
													<option value="1">Никогда (проживает в другом городе)</option>
													<option value="2">1 раз в месяц (при получении оплаты)</option>
													<option value="3">Периодически (чаще 1 раза в месяц)</option>
													<option value="4">Постоянно (проживает в этой же квартире)</option>
												</select>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Какую ответственность за состояние и ремонт объекта берет на себя собственник:
											</div>
											<div class="objectDescriptionBody" style="min-width: 330px">
												<textarea name="responsibility" maxlength="1000" rows="7" cols="43"></textarea>
											</div>
										</div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel">
												Важные моменты, касающиеся сдаваемого объекта, которые не были указаны в форме выше:
											</div>
											<div class="objectDescriptionBody" style="min-width: 330px">
												<textarea name="comment" maxlength="1000" rows="7" cols="43"></textarea>
											</div>
										</div>
									</div>

									<div class="advertDescriptionChapter" id="submitAdvertButton">
										<div class="advertDescriptionChapterHeader"></div>
										<div class="objectDescriptionItem">
											<div class="objectDescriptionItemLabel"></div>
											<div class="objectDescriptionBody">
												<button class="saveAdvertButton">
													Сохранить
												</button>
												<!-- При нажатии - форма проверяется на заполненность и выдаются красные предупреждения, отправляется ан сервер и сохраняется как есть, перематывается вверх плавно к заголовку объявления - выдается модальное окно с результатом -->
											</div>
										</div>
									</div>
								</form>
							</div>
							<div class="news advertForPersonalPage unpublished">
								<div class="newsHeader">
									<span class="advertHeaderAddress">Квартира по улице Кирова 15, №3</span>
									<div class="advertHeaderStatus">
										статус: не опубликовано
									</div>
								</div>
								<div class="fotosWrapper">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div>
								<ul class="setOfInstructions">
									<li>
										<a href="#">удалить</a>
									</li>
									<li>
										<a href="#">редактировать</a>
									</li>
									<li>
										<a href="#">подробнее</a>
									</li>
									<li>
										<a href="#">опубликовать</a>
									</li>
								</ul>
								<ul class="listDescription">
									<li>
										<span class="headOfString" style="vertical-align: top;">Заинтересовавшиеся арендаторы:</span><a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>
									</li>
									<li>
										<span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
									</li>
									<li>
										<span class="headOfString">Единовременная комиссия:</span>
										<a href="#"> 3000 руб. (40%) собственнику</a>
									</li>
									<li>
										<span class="headOfString">Адрес:</span>
										улица Посадская 51
									</li>
									<li>
										<span class="headOfString">Количество комнат:</span>
										2, смежные
									</li>
									<li>
										<span class="headOfString">Площадь (жилая/общая):</span>
										22.4/34 м²
									</li>
									<li>
										<span class="headOfString">Этаж:</span>
										3 из 10
									</li>
									<li>
										<span class="headOfString">Срок сдачи:</span>
										долгосрочно
									</li>
									<li>
										<span class="headOfString">Мебель:</span>
										есть
									</li>
									<li>
										<span class="headOfString">Район:</span>
										Центр
									</li>
									<li>
										<span class="headOfString">Телефон собственника:</span>
										89221431615, <a href="#">Алексей Иванович</a>
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
							<div class="news advertForPersonalPage published">
								<div class="newsHeader">
									<span class="advertHeaderAddress">Квартира по улице Кирова 15, №3</span>
									<div class="advertHeaderStatus">
										статус: опубликовано
									</div>
								</div>
								<div class="fotosWrapper">
									<div class="middleFotoWrapper">
										<img class="middleFoto" src="">
									</div>
								</div>
								<ul class="setOfInstructions">
									<li>
										<a href="#">редактировать</a>
									</li>
									<li>
										<a href="#">подробнее</a>
									</li>
									<li>
										<a href="#">снять с публикации</a>
									</li>
								</ul>
								<ul class="listDescription">
									<li>
										<span class="headOfString" style="vertical-align: top;">Заинтересовавшиеся арендаторы:</span><a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>, <a style="text-decoration: none;" href="man.php">Алексей Мухмаев</a>
									</li>
									<li>
										<span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.
									</li>
									<li>
										<span class="headOfString">Единовременная комиссия:</span>
										<a href="#"> 3000 руб. (40%) собственнику</a>
									</li>
									<li>
										<span class="headOfString">Адрес:</span>
										улица Посадская 51
									</li>
									<li>
										<span class="headOfString">Количество комнат:</span>
										2, смежные
									</li>
									<li>
										<span class="headOfString">Площадь (жилая/общая):</span>
										22.4/34 м²
									</li>
									<li>
										<span class="headOfString">Этаж:</span>
										3 из 10
									</li>
									<li>
										<span class="headOfString">Срок сдачи:</span>
										долгосрочно
									</li>
									<li>
										<span class="headOfString">Мебель:</span>
										есть
									</li>
									<li>
										<span class="headOfString">Район:</span>
										Центр
									</li>
									<li>
										<span class="headOfString">Телефон собственника:</span>
										89221431615, <a href="#">Алексей Иванович</a>
									</li>
								</ul>
								<div class="clearBoth"></div>
							</div>
						</div>
						<div id="tabs-4">
							<div class="shadowText">
								На этой вкладке Вы можете задать параметры, в соответствии с которыми ресурс Хани Хом будет осуществлять автоматический поиск объявлений на портале и будет оповещать Вас о появлении новых объектов по указанному в профиле e-mail
							</div>
							<div id="notEditingSearchParametersBlock" class="objectDescription">
								<div class="setOfInstructions">
									<a href="#">редактировать</a>
									<br>
								</div>
								<fieldset class="notEdited">
									<legend>
										Характеристика объекта
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Тип:</td>
												<td class="objectDescriptionBody"><span>квартира</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Количество комнат:</td>
												<td class="objectDescriptionBody"><span>1, 2</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Комнаты смежные:</td>
												<td class="objectDescriptionBody"><span>только изолированные</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Этаж:</td>
												<td class="objectDescriptionBody"><span>не первый</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">С мебелью и бытовой техникой:</td>
												<td class="objectDescriptionBody"><span>нет</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>
								<fieldset class="notEdited">
									<legend>
										Стоимость
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Арендная плата в месяц от:</td>
												<td class="objectDescriptionBody"><span>0</span> руб.</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Арендная плата в месяц до:</td>
												<td class="objectDescriptionBody"><span>20000</span> руб.</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Залог до:</td>
												<td class="objectDescriptionBody"><span>25000</span> руб.</td>
											</tr>
										</tbody>
									</table>
								</fieldset>
								<fieldset class="notEdited" id="additionalSearchDescription">
									<legend>
										Особые параметры поиска
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel" id="firstTableColumnSpecial">Как собираетесь проживать:</td>
												<td class="objectDescriptionBody"><span>семейная пара</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Ссылки на страницы сожителей:</td>
												<td class="objectDescriptionBody"><span>20000ывывыа выапваппв ывавыаывапы ываывпаыв</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Дети:</td>
												<td class="objectDescriptionBody"><span>С детьми младше 4-х лет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Количество детей и их возраст:</td>
												<td class="objectDescriptionBody"><span>2 ребенка по 2 и 6 лет</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Животные:</td>
												<td class="objectDescriptionBody"><span>С животным(ми)</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Количество животных и их вид:</td>
												<td class="objectDescriptionBody"><span>1 кошка</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Ориентировочный срок аренды:</td>
												<td class="objectDescriptionBody"><span>долгосрочно</span></td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Дополнительные условия поиска:</td>
												<td class="objectDescriptionBody"><span>Хотелось бы жить рядом с парком: Зеленая роща или 50 лет ВЛКСМ, чтобы можно было по утрам бегать и заниматься спортом. У меня уже несколько олимпийских медалей и я хочу получить еще одну</span></td>
											</tr>
										</tbody>
									</table>
								</fieldset>
								<fieldset class="notEdited">
									<legend>
										Район
									</legend>
									<table>
										<tbody>
											<tr>
												<td class="objectDescriptionItemLabel">Академический</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">Юго-Западный</td>
											</tr>
											<tr>
												<td class="objectDescriptionItemLabel">ВИЗ</td>
											</tr>
										</tbody>
									</table>
								</fieldset>
							</div>
							<form name="searchParameters" id="extendedSearchParametersBlock" style="display: none;">
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
								</div><!-- /end.rightBlockOfSearchParameters -->

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
										<span class="searchItemLabel">Ориентировочный срок аренды: </span>
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
								<div class="clearBoth"></div>
								<input type="submit" value="Сохранить" id="saveSearchParameters" class="bottomButton button">
								<div class="clearBoth"></div>
							</form><!-- /end.extendedSearchParametersBlock -->

						</div><!-- /end.tabs-4 -->
						<div id="tabs-5">
							<div class="shadowText">
								На этой вкладке расположены все объявления, добавленные Вами в избранные
							</div>
							<div class="choiceViewSearchResult">
								<span id="expandList"><a href="#">Список</a>&nbsp;&nbsp;&nbsp;</span><span id="listPlusMap"><a href="#">Список + карта</a>&nbsp;&nbsp;&nbsp;</span><span id="expandMap"><a href="#">Карта</a></span>
							</div>
							<div id="resultOnSearchPage" style="height: 100%;">

								<!-- Информация об объектах, подходящих условиям поиска -->
								<table class="listOfRealtyObjects" id="shortListOfRealtyObjects">
									<tbody>
										<tr class="realtyObject" coordX="56.836396" coordY="60.588662" balloonContentBody='<div class="headOfBalloon">ул. Ленина 13</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
											<td>
											<div class="numberOfRealtyObject">
												1
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Ленина 13
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 15000 </td>
										</tr>
										<tr class="realtyObject" coordX="56.819927" coordY="60.539264" balloonContentBody='<div class="headOfBalloon">ул. Репина 105</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
											<td>
											<div class="numberOfRealtyObject">
												2
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Репина 105
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 35000 </td>
										</tr>
										<tr class="realtyObject" coordX="56.817405" coordY="60.558452" balloonContentBody='<div class="headOfBalloon">ул. Шаумяна 107</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
											<td>
											<div class="numberOfRealtyObject">
												3
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Шаумяна 107
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 150000 </td>
										</tr>
										<tr class="realtyObject" coordX="56.825483" coordY="60.57357" balloonContentBody='<div class="headOfBalloon">ул. Гурзуфская 38</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
											<td>
											<div class="numberOfRealtyObject">
												123
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Гурзуфская 38
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 6000 </td>
										</tr>
										<tr class="realtyObject" coordX="56.820769" coordY="60.560742" balloonContentBody='<div class="headOfBalloon">ул. Серафимы Дерябиной 17</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
											<td>
											<div class="numberOfRealtyObject">
												1254
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Серафимы Дерябиной 17
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 2000 </td>
										</tr>
										<tr class="realtyObject" coordX="56.820769" coordY="60.560742" balloonContentBody='<div class="headOfBalloon">ул. Серафимы Дерябиной 17</div><div class="fotosWrapper"><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div><div class="middleFotoWrapper"><img class="middleFoto" src=""></div></div><ul class="listDescription"><li><span class="headOfString">Тип:</span> Квартира</li><li><span class="headOfString">Плата за аренду:</span> 15000 + коммунальные услуги от 1500 до 2500 руб.</li><li><span class="headOfString">Единовременная комиссия:</span><a href="#"> 3000 руб. (40%) собственнику</a></li><li><span class="headOfString">Адрес:</span> улица Посадская 51</li><li><span class="headOfString">Количество комнат:</span> 2, смежные</li><li><span class="headOfString">Площадь (жилая/общая):</span> 22.4/34 м²</li><li><span class="headOfString">Этаж:</span> 3 из 10</li><li><span class="headOfString">Срок сдачи:</span> долгосрочно</li><li><span class="headOfString">Мебель:</span> есть</li><li><span class="headOfString">Район:</span> Центр</li><li><span class="headOfString">Телефон собственника:</span> 89221431615, <a href="#">Алексей Иванович</a></li></ul><div class="clearBoth"></div><div style="width:100%;"><a href="descriptionOfObject.html">Подробнее</a><div style="float: right; cursor: pointer;"><div class="blockOfIcon"><a><img class="icon" title="Добавить в избранное" src="img/blue_star.png"></a></div><a id="addToFavorit"> добавить в избранное</a></div></div>'>
											<td>
											<div class="numberOfRealtyObject">
												12
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Серафимы Дерябиной 17
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 350000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="numberOfRealtyObject">
												15
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>улица Сибирский тракт 50 летия 107
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 15000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="numberOfRealtyObject">
												15
											</div>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Сумасранка 4
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 35000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Серафимы Дерябиной 154
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 150000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Белореченская 24
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 6000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Маврода 2012
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 2000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Пискуна 1
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 350000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>улица Сибирский тракт 50 летия 107
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 15000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Сумасранка 4
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 35000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Серафимы Дерябиной 154
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 150000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Белореченская 24
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 6000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Маврода 2012
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 2000 </td>
										</tr>
										<tr class="realtyObject">
											<td>
											<div class="blockOfIcon">
												<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
											</div></td>
											<td>
											<div class="fotosWrapper resultSearchFoto">
												<div class="middleFotoWrapper">
													<img class="middleFoto" src="">
												</div>
											</div></td>
											<td>ул. Пискуна 1
											<div class="linkToDescriptionBlock">
												<a class="linkToDescription" href="descriptionOfObject.php">Подробнее</a>
											</div></td>
											<td> 350000 </td>
										</tr>
									</tbody>
								</table>

								<!-- Область показа карты -->
								<div id="map"></div>

								<div class="clearBoth"></div>

								<!-- Первоначально скрытый раздел с подробным списком объявлений-->
								<div id="fullParametersListOfRealtyObjects" style="display: none;">
									<table class="listOfRealtyObjects" style="width: 100%; float:none;">
										<thead>
											<tr class="listOfRealtyObjectsHeader">
												<th class="top left"></th>
												<th> Фото </th>
												<th> Адрес </th>
												<th> Район </th>
												<th> Комнат </th>
												<th> Площадь </th>
												<th> Этаж </th>
												<th class="top right"> Цена, руб. </th>
											</tr>
										</thead>
										<tbody>
											<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
												<td>
												<div class="numberOfRealtyObject">
													15
												</div>
												<div class="blockOfIcon">
													<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
												</div></td>
												<td>
												<div class="fotosWrapper resultSearchFoto">
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
												</div></td>
												<td>ул. Серафимы Дерябиной 17</td>
												<td> ВИЗ </td>
												<td> 2 </td>
												<td> 22.4/34 </td>
												<td> 2/13</td>
												<td> 15000 </td>
											</tr>
											<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
												<td>
												<div class="numberOfRealtyObject">
													15
												</div>
												<div class="blockOfIcon">
													<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
												</div></td>
												<td>
												<div class="fotosWrapper resultSearchFoto">
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
												</div></td>
												<td>ул. Гурзуфская 38</td>
												<td> ВИЗ </td>
												<td> 2 </td>
												<td> 22.4/34 </td>
												<td> 2/13</td>
												<td> 15000 </td>
											</tr>
											<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
												<td>
												<div class="numberOfRealtyObject">
													15
												</div>
												<div class="blockOfIcon">
													<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
												</div></td>
												<td>
												<div class="fotosWrapper resultSearchFoto">
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
												</div></td>
												<td>ул. Шаумяна 107</td>
												<td> ВИЗ </td>
												<td> 2 </td>
												<td> 22.4/34 </td>
												<td> 2/13</td>
												<td> 15000 </td>
											</tr>
											<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
												<td>
												<div class="numberOfRealtyObject">
													15
												</div>
												<div class="blockOfIcon">
													<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
												</div></td>
												<td>
												<div class="fotosWrapper resultSearchFoto">
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
												</div></td>
												<td>ул. Репина 105</td>
												<td> ВИЗ </td>
												<td> 2 </td>
												<td> 22.4/34 </td>
												<td> 2/13</td>
												<td> 15000 </td>
											</tr>
											<tr class="realtyObject" linkToDescription="descriptionOfObject.html">
												<td>
												<div class="blockOfIcon">
													<a><img class="icon" title="Удалить из избранного" src="img/gold_star.png"></a>
												</div></td>
												<td>
												<div class="fotosWrapper resultSearchFoto">
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
													<div class="middleFotoWrapper">
														<img class="middleFoto" src="">
													</div>
												</div></td>
												<td>ул. Ленина 13</td>
												<td> ВИЗ </td>
												<td> 2 </td>
												<td> 22.4/34 </td>
												<td> 2/13</td>
												<td> 15000 </td>
											</tr>
										</tbody>
									</table>
								</div>
							</div><!-- /end.resultOnSearchPage -->
						</div>
					</div>
				</div>

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

		<!-- Загружаем библиотеку для работы с картой от Яндекса -->
		<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

		<!-- scripts concatenated and minified via build script -->
		<script src="js/main.js"></script>
		<script src="js/personal.js"></script>

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