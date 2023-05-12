<?php
    require("../vendor/autoload.php");

    use OTPHP\TOTP;

    if($_GET['mode'] == "register") {
        // A random secret will be generated from this.
        // You should store the secret with the user for verification.
        $otp = TOTP::generate();
        $secret = $otp->getSecret();
        //echo "The OTP secret is: {$secret}\n";

        $otp = TOTP::createFromSecret($secret);
        //echo "The current OTP is: {$otp->now()}\n";

        $otp->setLabel('PasswordVault');
        echo $otp->getProvisioningUri();
    }
?>