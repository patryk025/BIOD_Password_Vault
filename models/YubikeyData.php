<?php

namespace models;

class YubikeyData {
    private $id;
    private $id_user;
    private $credential_public_key;
    private $certificate;
    private $certificate_issuer;
    private $certificate_subject;
    private $created;

    public function __construct($fields) {
        $this->id = $fields['id'];
        $this->id_user = $fields['id_user'];
        $this->credential_public_key = $fields['credential_public_key'];
        $this->certificate = $fields['certificate'];
        $this->certificate_issuer = $fields['certificate_issuer'];
        $this->certificate_subject = $fields['certificate_subject'];
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

        return $this;
    }

    public function getCredentialPublicKey()
    {
        return $this->credential_public_key;
    }

    public function setCredentialPublicKey($credential_public_key): self
    {
        $this->credential_public_key = $credential_public_key;

        return $this;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function setCertificate($certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }

    public function getCertificateIssuer()
    {
        return $this->certificate_issuer;
    }

    public function setCertificateIssuer($certificate_issuer): self
    {
        $this->certificate_issuer = $certificate_issuer;

        return $this;
    }

    public function getCertificateSubject()
    {
        return $this->certificate_subject;
    }

    public function setCertificateSubject($certificate_subject): self
    {
        $this->certificate_subject = $certificate_subject;

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

    public static function createYubikey($user, $credential_public_key, $certificate, $certificate_issuer, $certificate_subject) {
        $instance = new self();
        $instance->id_user = $user->getId();
        $instance->credential_public_key = $credential_public_key;
        $instance->certificate = $certificate;
        $instance->certificate_issuer = $certificate_issuer;
        $instance->certificate_subject = $certificate_subject;
        $instance->created = date('Y-m-d H:i:s');
        return $instance;
    }
}
?>