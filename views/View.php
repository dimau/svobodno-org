<?php

    class View
    {
        public function __construct() {

        }

        public function generate($templ, $dataArr) {

            include "templates/".$templ;

        }
    }
