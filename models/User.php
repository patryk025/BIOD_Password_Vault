<?php

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

    public function __construct($fields) {
        $this->id = $fields['id'];
        $this->email = $fields['email'];
        $this->password = $fields['password'];
        $this->password_salt = $fields['password_salt'];
        $this->is_verified = $fields['is_verified'];
        $this->created = $fields['created'];
        $this->updated = $fields['updated'];
    }

    public function verifyUser($code) {
        $this->is_verified = true;
        DbAdapter::editAttributeInObject('users', 'is_verified', "1", $this->id, 'id');
    }
    
    public function getYubikeyData() {
        return DbAdapter::queryObjects('yubikey_data', $id, 'id_user');
    }

    public function getOTPData() {
        return DbAdapter::queryObjects('otp_secrets', $id, 'id_user');
    }

    public function getPasswords() {
        return DbAdapter::queryObjects('vaults', $id, 'id_user');
    }
}

?>