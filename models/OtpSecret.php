<?php

class OtpSecret extends Model {
    private $id;
    private $user_id;
    private $encrypted_secret;
    private $created;

    public function __construct($fields) {
        $this->id = $fields['id'];
        $this->user_id = $fields['user_id'];
        $this->encrypted_secret = $fields['encrypted_secret'];
        $this->created = $fields['created'];
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
        return $this->user_id;
    }

    public function setUserId($user_id): self
    {
        $this->user_id = $user_id;
        DbAdapter::editAttributeInObject('otp_secret', 'user_id', $user_id, $this->id, 'id');
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
}