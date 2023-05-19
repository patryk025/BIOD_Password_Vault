<?php

require_once "Model.php";

class OtpSecret extends Model {
    private $id;
    private $id_user;
    private $encrypted_secret;
    private $created;

    public function __construct($fields = null) {
        if($fields != null) {
            $this->id = $fields['id'];
            $this->id_user = $fields['id_user'];
            $this->encrypted_secret = $fields['encrypted_secret'];
            $this->created = $fields['created'];
        }
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

    public function getUserId()
    {
        return $this->id_user;
    }

    public function setUserId($id_user): self
    {
        $this->id_user = $id_user;
        DbAdapter::editAttributeInObject('otp_secret', 'id_user', $id_user, $this->id, 'id');
        return $this;
    }

    public function getEncryptedSecret()
    {
        return $this->encrypted_secret;
    }

    public function setEncryptedSecret($encrypted_secret): self
    {
        $this->encrypted_secret = $encrypted_secret;
        DbAdapter::editAttributeInObject('otp_secret', 'encrypted_secret', $encrypted_secret, $this->id, 'id');
        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created): self
    {
        $this->created = $created;
        DbAdapter::editAttributeInObject('otp_secret', 'created', $created, $this->id, 'id');
        return $this;
    }

    public static function createSecret($user, $encrypted_secret) {
        $instance = new self();
        $instance->id_user = $user->getId();
        $instance->encrypted_secret = $encrypted_secret;
        $instance->created = date('Y-m-d H:i:s');
        return $instance;
    }
}
?>