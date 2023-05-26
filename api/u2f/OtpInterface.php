<?php
    session_start();
    require(__DIR__."/../../vendor/autoload.php");
    require_once __DIR__."/../../db/DbAdapter.php";

    header('Content-Type: application/json');

    use OTPHP\TOTP;
    use Endroid\QrCode\Builder\Builder;
    use Endroid\QrCode\Encoding\Encoding;
    use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
    use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
    use Endroid\QrCode\Label\Font\NotoSans;
    use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
    use Endroid\QrCode\Writer\PngWriter;

    if($_GET['mode'] == "register") {
        // A random secret will be generated from this.
        // You should store the secret with the user for verification.
        $otp = TOTP::generate();
        $secret = $otp->getSecret();
        //echo "The OTP secret is: {$secret}\n";

        $otp = TOTP::createFromSecret($secret);
        //echo "The current OTP is: {$otp->now()}\n";

        $otp->setLabel('PasswordVault');
        //echo $otp->getProvisioningUri();

        $dataToSend = [];
        $dataToSend['secret'] = $secret;
        $_SESSION['otp_secret'] = $secret;
        $_SESSION['otp_confirmed'] = false;

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($otp->getProvisioningUri())
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->validateResult(false)
            ->build();

        $imageData = base64_encode($result->getString());

        $dataToSend['qr_code'] = $imageData;
        echo json_encode($dataToSend);
    }
    else if($_GET['mode'] == "verify") {
        if(isset($_POST['email'])) {
            $user = DbAdapter::queryObject('users', $_POST['email'], 'email');
            $_SESSION['otp_secret'] = $user->getOTPData()[0]->getEncryptedSecret();
        }
        $otp = TOTP::createFromSecret($_SESSION['otp_secret']); // create TOTP object from the secret.
        $val_result = $otp->verify($_POST['code'], null, 10);
        $_SESSION['otp_confirmed'] = $val_result;
        echo json_encode(array('valid'=>$val_result));
    }
?>