<?php

foreach (glob(__DIR__."/../models/*.php") as $filename)
{
    require_once $filename;
}

session_start();

if (!empty($_SERVER['HTTP_CSRFTOKEN'])) {
    if (hash_equals($_SESSION['token'], $_SERVER['HTTP_CSRFTOKEN'])) {
        if(isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $passwords = $user->getPasswords();
            $passwords_json = [];
            foreach($passwords as $password) {
                $tmp_obj = [];
                $tmp_obj['portal'] = $password->getUrl(); 
                $tmp_obj['login'] = $password->getLogin(); 
                $tmp_obj['password'] = $password->getPassword(); 
                $passwords_json[] = $tmp_obj;
            }
            echo json_encode(array("error"=>false, "passwords"=>$passwords_json));
        }
        else {
            echo json_encode(array("error"=>true, "msg"=>"Użytkownik nie jest zalogowany"));
        }
    } else {
        header('HTTP/1.0 403 Forbidden');
        echo json_encode(array("error"=>true, "msg"=>"Próbuj dalej"));
    }
} else {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array("error"=>true, "msg"=>"You shall not pass!"));
}

?>