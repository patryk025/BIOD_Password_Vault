<?php

class Passwords extends Model {
    private $id;
    private $id_user;
    private $url;
    private $login;
    private $password;
    private $created;
    private $changed;

    public function __construct($fields = null) {
        if($fields != null) {
            $this->id = $fields['id'];
            $this->id_user = $fields['id_user'];
            $this->url = $fields['url'];
            $this->login = $fields['login'];
            $this->password = $fields['password'];
            $this->created = $fields['created'];
            $this->changed = $fields['changed'];
        }
    }

    public static function createPassword($id_user, $url, $login, $password) {
        $instance = new self();
        $instance->id_user = $id_user;
        $instance->url = $url;
        $instance->login = $login;
        $instance->password = $password;
        $instance->created = date('Y-m-d H:i:s');
        $instance->updated = date('Y-m-d H:i:s');

        $this->encryptPassword();

        return $instance;
    }

    private function encryptData($data, $key) {
        $method = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv.$encrypted);
    }

    function decryptData($data, $key)
    {
        $method = "AES-256-CBC";
        $data = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $ivlen);
        $encrypted = substr($data, $ivlen);
        $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    private function generateKey($user) {
        $user_data = $user->getId() . $user->getPassword() . $user->getPasswordSalt() . $user->getUpdated() . $user->getCreated . $user->getUpdated();

        $key = strrev($user_data);
        $key = scrambleString($key);
        $key = hash('sha256', $key);
        $key = scrambleString($key);
        $key = strrev($key);
        return $key;
    }

    private function scrambleString($string) {
        $length = strlen($string);

        for ($i = 0; $i < $length - 1; $i += 2) {
            $temp = $string[$i];
            $string[$i] = $string[$i+1];
            $string[$i+1] = $temp;
        }

        return $string;
    }

    private function encryptPassword() {
        $user = DbAdapter::queryObject('users', $this->id_user);

        $key = $this->generateKey();
        
        $this->url = $this->encryptData($this->url, $key);
        $this->login = $this->encryptData($this->login, $key);
        $this->password = $this->encryptData($this->password, $key);
        
    }

    private function decryptPassword() {
        $user = DbAdapter::queryObject('users', $this->id_user);

        $key = $this->generateKey();
        
        $this->url = $this->decryptData($this->url, $key);
        $this->login = $this->decryptData($this->login, $key);
        $this->password = $this->decryptData($this->password, $key);
        
    }
}
