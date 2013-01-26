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

} // Если ошибок не было и пользователь еще не отправлял заявку на просмотр
elseif ($isLoggedIn === FALSE || $userCharacteristic['typeTenant'] === FALSE || $signUpToViewData['status'] == "") {
    echo "  <li>
                <button class='mainButton signUpToViewButton'>Записаться на просмотр</button>
            </li>
         ";
} elseif ($signUpToViewData['status'] == "Новая") {
    echo " <li>
                <div class='signUpToViewStatusBlock inProgress' title='оператор свяжется с Вами в ближайшее время'>Заявка отправлена</div>
           </li>
         ";
} elseif ($signUpToViewData['status'] == "Назначен просмотр") {
    echo "  <li>
                <div class='signUpToViewStatusBlock confirmed'>Назначен просмотр<br>{$propertyCharacteristic['earliestDate']} в {$propertyCharacteristic['earliestTimeHours']}:{$propertyCharacteristic['earliestTimeMinutes']}</div>
            </li>
         ";
} elseif ($signUpToViewData['status'] == "Отложена") {
    echo " <li>
                <div class='signUpToViewStatusBlock confirmed' title='если ближайший просмотр будет перенесен или по его окончании не будет заключено договора аренды, с Вами свяжется оператор для назначения следующей даты просмотра'>Заявка отложена</div>
           </li>
         ";
} elseif ($signUpToViewData['status'] == "Объект уже сдан" || $signUpToViewData['status'] == "Отказ собственника") {
    echo " <li>
                <div class='signUpToViewStatusBlock failure' title='к сожалению, собственник отказался от показа, либо объект уже сдан'>Отказ собственника</div>
           </li>
         ";
} elseif ($signUpToViewData['status'] == "Отменена") {
    echo " <li>
                <div class='signUpToViewStatusBlock failure' title='Вы отказались от участия в просмотре объекта'>Заявка отменена</div>
           </li>
         ";
} elseif ($signUpToViewData['status'] == "Безуспешный просмотр") {
    echo " <li>
                <div class='signUpToViewStatusBlock failure' title='Вы участвовали в просмотре объекта, но договор аренды не заключили'>Безуспешный просмотр</div>
           </li>
         ";
} elseif ($signUpToViewData['status'] == "Успешный просмотр") {
    echo " <li>
                <div class='signUpToViewStatusBlock confirmed' title='Вы участвовали в просмотре объекта и заключили договор аренды'>Вы арендовали этот объект</div>
           </li>
         ";
} else {
    // Если ничего не подошло
}