<?php

require_once __DIR__."/Model.php";
require_once __DIR__."/../db/DbAdapter.php";

class User extends Model {
    private $id;
    private $email;
    private $password;
    private $password_salt;
    private $is_verified;
    private $created;
    private $updated;
    private $yubikeyData;
    private $OTPData;

    public function __construct($fields = null) {
        if($fields != null) {
            $this->id = $fields['id'];
            $this->email = $fields['email'];
            $this->password = $fields['password'];
            $this->password_salt = $fields['password_salt'];
            $this->is_verified = $fields['is_verified'];
            $this->created = $fields['created'];
            $this->updated = $fields['updated'];
        }
    }

    public function verifyUser() {
        $this->is_verified = true;
        DbAdapter::editAttributeInObject('users', 'is_verified', "1", $this->id, 'id');
        $this->updated = date('Y-m-d H:i:s');
        DbAdapter::editAttributeInObject('users', 'updated', $this->updated, $this->id, 'id');
    }
    
    public function getYubikeyData() {
        return DbAdapter::queryObjects('yubikey_data', $this->id, 'id_user');
    }

    public function getOTPData() {
        return DbAdapter::queryObjects('otp_secrets', $this->id, 'id_user');
    }

    public function getPasswords() {
        return DbAdapter::queryObjects('passwords', $this->id, 'id_user');
    }

    public static function createUser($email, $password) {
        $instance = new self();
        $instance->email = $email;
        $instance->password_salt = bin2hex(random_bytes(16));
        $instance->password = password_hash($password.":".$instance->password_salt, PASSWORD_DEFAULT);
        $instance->is_verified = 0;
        $instance->created = date('Y-m-d H:i:s');
        $instance->updated = date('Y-m-d H:i:s');
        return $instance;
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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password): self
    {
        $this->password = password_hash($password.":".$this->password_salt, PASSWORD_DEFAULT);

        return $this;
    }

    public function getPasswordSalt()
    {
        return $this->password_salt;
    }

    public function setPasswordSalt($password_salt): self
    {
        $this->password_salt = $password_salt;

        return $this;
    }

    public function getIsVerified()
    {
        return $this->is_verified;
    }

    public function setIsVerified($is_verified): self
    {
        $this->is_verified = $is_verified;

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

    public function getUpdated()
    {
        return $this->updated;
    }

    public function setUpdated($updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}

?>