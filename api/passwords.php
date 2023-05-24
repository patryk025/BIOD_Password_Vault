<?php
// Załaduj pliki modeli
foreach (glob(__DIR__."/../models/*.php") as $filename) {
    require_once $filename;
}

// Rozpocznij sesję
session_start();

// Sprawdź token CSRF
if (!empty($_SERVER['HTTP_CSRFTOKEN'])) {
    if (!hash_equals($_SESSION['token'], $_SERVER['HTTP_CSRFTOKEN'])) {
        header('HTTP/1.0 403 Forbidden');
        echo json_encode(array("error"=>true, "msg"=>"Próbuj dalej"));
        exit;
    }
} else {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array("error"=>true, "msg"=>"You shall not pass!"));
    exit;
}

// Sprawdź, czy użytkownik jest zalogowany
if(!isset($_SESSION['user'])) {
    echo json_encode(array("error"=>true, "msg"=>"Użytkownik nie jest zalogowany"));
    exit;
}

// Pobierz użytkownika z sesji
$user = $_SESSION['user'];

// Obsłuż różne metody HTTP
switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':
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
        break;
    case 'POST':
        $password = Passwords::createPassword($user->getId(), $_POST['portal'], $_POST['login'], $_POST['password']);
        $result = $password->create();
        if($result) {
            echo json_encode(array("error"=>false));
        }
        else {
            echo json_encode(array("error"=>true, "msg"=>"Wystąpił błąd podczas usuwania hasła"));
        }
        break;
    case 'DELETE':
        DbAdapter::removeObject('passwords', $_GET['id']);
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
        echo json_encode(array("error"=>true, "msg"=>"Metoda nie jest obsługiwana"));
        exit;
}


?>