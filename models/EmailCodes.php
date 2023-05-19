<?php

require_once "Model.php";

class EmailCodes extends Model {
    private $id;
    private $user_id;
    private $identifier;
    private $valid_code;
    private $valid_from;
    private $valid_to;
    
    public function __construct($user_id, $identifier, $valid_code) {
        $this->user_id = $user_id;
        $this->identifier = $identifier;
        $this->valid_code = $valid_code;
        
        $this->valid_from = new DateTime(); 

        $valid_to = clone $this->valid_from; 
        $valid_to->add(new DateInterval('PT15M'));
        $this->valid_to = $valid_to;
    }
    
}