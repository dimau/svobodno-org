<?php
/* Базовый класс, содержащий общие параметры и методы для классов, представляющих собой модели пользователя: UserFull и UserIncoming */

class User {
    protected $id = "";
    protected $name = "";
    protected $secondName = "";
    protected $surname = "";
    protected $email = "";
    protected $telephon = "";
    protected $favoritePropertiesId = array();
    protected $typeTenant = FALSE;
    protected $typeOwner = FALSE;
    protected $typeAdmin = FALSE;
    protected $reviewFull = 0;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSecondName() {
        return $this->secondName;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getTelephon() {
        return $this->telephon;
    }

    // Метод возвращает массив идентификаторов избранных объявлений текущего пользователя (если он не авторизован, то пустой массив)
    public function getFavoritePropertiesId() {
        return $this->favoritePropertiesId;
    }

    // Является ли пользователь арендатором (то есть имеет действующий поисковый запрос или регистрируется в качестве арендатора)
    public function isTenant() {
        return $this->typeTenant;
    }

    // Является ли пользователь собственником (то есть имеет хотя бы 1 объявление или регистрируется в качестве собственника)
    public function isOwner() {
        return $this->typeOwner;
    }

    // Является ли пользователь администратором. Возвращает ассоциированный массив с правами доступа
    // Если пользователь не является администратором, то все права у него будут с флагами FALSE
    public function isAdmin() {
        if ($this->typeAdmin == FALSE) return array('newOwner' => FALSE, 'newAdvertAlien' => FALSE, 'searchUser' => FALSE);

        if (substr($this->typeAdmin, 0, 1) == "1") $result['newOwner'] = TRUE; else $result['newOwner'] = FALSE;
        if (substr($this->typeAdmin, 1, 1) == "1") $result['newAdvertAlien'] = TRUE; else $result['newAdvertAlien'] = FALSE;
        if (substr($this->typeAdmin, 2, 1) == "1") $result['searchUser'] = TRUE; else $result['searchUser'] = FALSE;

        return $result;
    }

    public function getReviewFull() {
        return $this->reviewFull;
    }
}
