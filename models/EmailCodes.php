<?php

require_once __DIR__."/Model.php";

class EmailCodes extends Model {
    private $id;
    private $id_user;
    private $identifier;
    private $valid_code;
    private $valid_from;
    private $valid_to;
    
    public function __construct($fields = null) {
        if($fields != null) {
            $this->id_user = $fields['id_user'];
            $this->identifier = $fields['identifier'];
            $this->valid_code = $fields['valid_code'];
            $this->valid_from = $fields['valid_from'];
            $this->valid_to = $fields['valid_to'];
        }
    }

    public static function createEmail($user, $identifier, $valid_code) {
        $instance = new self();

        $instance->id_user = $user->getId();
        $instance->identifier = $identifier;
        $instance->valid_code = $valid_code;
        
        $instance->valid_from = new DateTime(); 

        $valid_to = clone $this->valid_from; 
        $valid_to->add(new DateInterval('PT15M'));

        $instance->valid_from = $this->valid_from->format('Y-m-d H:i:s');
        $instance->valid_to = $valid_to->format('Y-m-d H:i:s');

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

    public function getIdUser()
    {
        return $this->id_user;
    }

    public function setIdUser($id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getValidCode()
    {
        return $this->valid_code;
    }

    public function setValidCode($valid_code): self
    {
        $this->valid_code = $valid_code;

        return $this;
    }

    public function getValidFrom()
    {
        return $this->valid_from;
    }

    public function setValidFrom($valid_from): self
    {
        $this->valid_from = $valid_from;

        return $this;
    }

    public function getValidTo()
    {
        return $this->valid_to;
    }

    public function setValidTo($valid_to): self
    {
        $this->valid_to = $valid_to;

        DbAdapter::editAttributeInObject('email_codes', 'valid_to', $valid_to, $this->id, 'id');

        return $this;
    }

    public function deactivate() {
        $this->setValidTo(date('Y-m-d H:i:s')); 
    }
}