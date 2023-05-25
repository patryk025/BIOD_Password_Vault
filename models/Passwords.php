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

        $instance->encryptPassword();

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

    public function generateKey($user) {
        $user_data = $user->getId() . $user->getPassword() . $user->getPasswordSalt() . $user->getUpdated() . $user->getCreated();

        $key = strrev($user_data);
        $key = $this->scrambleString($key);
        $key = hash('sha256', $key);
        $key = $this->scrambleString($key);
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

    public function encryptPassword() {
        $user = DbAdapter::queryObject('users', $this->id_user);

        $key = $this->generateKey($user);
        
        $this->url = $this->encryptData($this->url, $key);
        $this->login = $this->encryptData($this->login, $key);
        $this->password = $this->encryptData($this->password, $key);
        
    }

    public function decryptPassword() {
        $user = DbAdapter::queryObject('users', $this->id_user);

        $key = $this->generateKey($user);
        
        $this->url = $this->decryptData($this->url, $key);
        $this->login = $this->decryptData($this->login, $key);
        $this->password = $this->decryptData($this->password, $key);
        
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getIdUser()
    {
        return $this->id_user;
    }

    public function setIdUser($id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getChanged()
    {
        return $this->changed;
    }

    public function setChanged($changed): self
    {
        $this->changed = $changed;

        return $this;
    }
}
