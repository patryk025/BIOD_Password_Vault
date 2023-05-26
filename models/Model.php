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
        return DbAdapter::insertObject(self::camelToSnake(get_class($this)), $this);
    }

    public function update() {
        return DbAdapter::updateObject(self::camelToSnake(get_class($this)), $this);
    }

    public function remove() {
        return DbAdapter::removeObject(self::camelToSnake(get_class($this)), $this);
    }
}