<?php
/**************************************
* Шаблон для Отображения контактов собственника / кнопки запроса контактов
* В зависимости от ситуации выдаем на страницу соответствуюущую кнопку, непосредственно контакты собственника и модальные окна
**************************************/
?>

<button class='mainButton getOwnerContactsButton'>
    Контакты собственника
</button>

<ul class="ownerContacts" style="list-style: none; padding: 0; line-height: 1em; display: none;">
    <li class="ownerContactsName"></li>
    <li class="ownerContactsTelephon"></li>
    <li class="ownerContactsSourceOfAdvert"><a class="ownerContactsSourceOfAdvertHref" href=""><a></li>
</ul>