<?php

class OtpSecret {
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
}