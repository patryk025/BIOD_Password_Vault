<?php

class YubikeyData {
    private $id;
    private $user_id;
    private $credential_public_key;
    private $certificate;
    private $certificate_issuer;
    private $certificate_subject;
    private $created;

    public function __construct($fields) {
        $this->id = $fields['id'];
        $this->user_id = $fields['user_id'];
        $this->credential_public_key = $fields['credential_public_key'];
        $this->certificate = $fields['certificate'];
        $this->certificate_issuer = $fields['certificate_issuer'];
        $this->certificate_subject = $fields['certificate_subject'];
        $this->created = $fields['created'];
    }
}
