<?php

require_once "botexception.class.php";

class db_exception extends BotException {
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}


