<?php

class Model {
    private function camelToSnake($string) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    public function create() {
        DbAdapter::insertObject(camelToSnake(get_class($this)), $this);
    }

    public function remove() {
        DbAdapter::removeObject(camelToSnake(get_class($this)), $this);
    }
}