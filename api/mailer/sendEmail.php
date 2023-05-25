<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use OTPHP\TOTP;

//Load Composer's autoloader
require __DIR__.'/../../vendor/autoload.php';

require_once __DIR__."/../../models/EmailCodes.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ ."/../../");
$dotenv->load();

function sendMail($email, $subject, $body, $alt_body) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAILER_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAILER_USER'];
        $mail->Password   = $_ENV['MAILER_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAILER_PORT'];
        $mail->CharSet    = "UTF-8";

        $mail->setFrom($_ENV['MAILER_ADDRESS'], 'Password Vault');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $alt_body;

        $mail->send();
        //return 'Message has been sent';
        return true;
    } catch (Exception $e) {
        //return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

function sendOneTimeCode($user, $uniqueId = null) {
    $totp = TOTP::create();
    $otp = $totp->now();
    $uniqueId = $uniqueId ?? uniqid();
    //$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $baseUrl = $_ENV['APP_URL'];

    $subject = 'Kod jednorazowy';
    $message = file_get_contents(__DIR__."/templates/one_time_code.html");
    $message = str_replace('{one_time_code}', $otp, $message);
    $message = str_replace('{domain_name}', $baseUrl, $message);
    $message = str_replace('{unique_id}', $uniqueId, $message);

    $alt_message = file_get_contents(__DIR__."/templates/one_time_code.txt");
    $alt_message = str_replace('{one_time_code}', $otp, $alt_message);
    $alt_message = str_replace('{domain_name}', $baseUrl, $alt_message);
    $alt_message = str_replace('{unique_id}', $uniqueId, $alt_message);

    if(sendMail($user->getEmail(), $subject, $message, $alt_message)) {
        DbAdapter::insertObject('email_codes', EmailCodes::createEmail($user, $uniqueId, $otp));
        return true;
    }
    else {
        return false;
    }

}

?>