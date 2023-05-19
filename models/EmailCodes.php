<?php

class EmailCodes extends Model {
    private $id;
    private $user_id;
    private $identifier;
    private $valid_code;
    private $valid_from;
    private $valid_to;

}