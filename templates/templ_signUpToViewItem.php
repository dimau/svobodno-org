<?php

    /**************************************
     * Алгоритм выбора HTML оформления статуса Запроса на просмотр и модального окна для запроса на просмотр
     *
     * Пользователь не авторизован {
     *      Кнопка Записаться на просмотр + модальное окно для неавторизованного пользователя
     * }
     * Пользователь авторизован {
     *      Пользователь не является арендатором {
     *          Кнопка Записаться на просмотр + модальное окно для пользователей не арендаторов
     *      }
     *      Пользователь является арендатором {
     *          Для этого пользователя и этого объекта недвижимости еще не создано Запроса на просмотр {
     *              Кнопка Записаться на просмотр + модальное окно с формой Записи на просмотр
     *          }
     *          Для этого пользователя и этого объекта недвижимости уже был создан Запрос на просмотр {
     *              Статус Запроса = confirmed {
     *                  Вместо кнопки Запроса инфа о времени просмотра + кнопка Изменить
     *              }
     *              Статус Запроса = failure {
     *                  Вместо кнопки Запроса инфа об отказе собственника
     *              }
     *              Статус Запроса = inProgress {
     *                  Вместо кнопки Запроса инфа о том, что Заявка обрабатывается
     *              }
     *          }
     *      }
     *}
     **************************************/

    // Если при передаче Запроса на показ возникли ошибки
    if ($statusOfSaveParamsToDB === FALSE) {
        echo "  <li>
                    <div class='signUpToViewStatusBlock error'>Ошибка при отправке запроса<br>Попробуйте еще раз немного позже</div>
                </li>
             ";

    } else { // Если ошибок не было

        if ($isLoggedIn === FALSE || $userCharacteristic['typeTenant'] === FALSE || $signUpToViewData['ownerStatus'] == "") {
            echo "  <li>
                        <button class='mainButton signUpToViewButton'>Записаться на просмотр</button>
                    </li>
                 ";
        }

        if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE && $signUpToViewData['ownerStatus'] == "confirmed") {
            echo "  <li>
                        <div class='signUpToViewStatusBlock confirmed'>Просмотр<br>{$signUpToViewData['finalDate']} в {$signUpToViewData['finalTimeHours']}:{$signUpToViewData['finalTimeMinutes']}</div>
                    </li>
                 ";
        }

        if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE && $signUpToViewData['ownerStatus'] == "failure") {
            echo " <li>
                       <div class='signUpToViewStatusBlock failure' title='к сожалению, собственник отказался от показа'>Отказ собственника</div>
                   </li>
                 ";
        }

        if ($isLoggedIn === TRUE && $userCharacteristic['typeTenant'] === TRUE && $signUpToViewData['ownerStatus'] == "inProgress") {
            echo " <li>
                       <div class='signUpToViewStatusBlock inProgress' title='оператор свяжется с Вами в ближайшее время'>Заявка отправлена</div>
                   </li>
                 ";
        }
    }