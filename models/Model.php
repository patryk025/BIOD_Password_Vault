<?php

class Model {
    private function camelToSnake($string) {
        /* druciarstwo */
        switch($string) {
            case "User":
                $string = "Users";
                break;
            case "OtpSecret":
                $string = "OtpSecrets";
                break;
        }
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    public function create() {
        DbAdapter::insertObject(self::camelToSnake(get_class($this)), $this);
    }

    public function remove() {
        DbAdapter::removeObject(self::camelToSnake(get_class($this)), $this);
    }
}