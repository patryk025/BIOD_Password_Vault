<?php

foreach (glob(__DIR__."/../models/*.php") as $filename)
{
    require_once $filename;
}

session_start();

if(isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    echo json_encode(array("error"=>false, "passwords"=>$user->getPasswords()));
}
else {
    echo json_encode(array("error"=>true, "msg"=>"Użytkownik nie jest zalogowany"));
}

?>