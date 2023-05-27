<?php
    foreach (glob(__DIR__."/../../models/*.php") as $filename)
    {
        require_once $filename;
    }

    session_start();

    $user = $_SESSION['user'];

    $addedOTP = false;
    $addedYubi = false;

    if(isset($_SESSION['otp_confirmed']) && $_SESSION['otp_confirmed'] && empty($user->getOTPData())) {
        $otp_secret = $_SESSION['otp_secret'];
        $otp = OtpSecret::createSecret($user, $otp_secret);
        $result = $otp->create();
        if(!is_bool($result)) {
            die(json_encode(array("error"=>true, "msg"=>"Wystąpił błąd podczas operacji na bazie danych.")));
        }
        else {
            $addedOTP = true;
        }
    }

    if(isset($_SESSION['registrations']) && empty($user->getYubikeyData())) {
        $yubi_data = $_SESSION['registrations'][0];

        $yubi_pubkey = $yubi_data->credentialPublicKey;
        $yubi_cert = $yubi_data->certificate;
        $yubi_cert_issuer = $yubi_data->certificateIssuer;
        $yubi_cert_subject = $yubi_data->certificateSubject;
        $yubi_cred_id = $yubi_data->credentialId;
        $yubi_rp_id = $yubi_data->rpId;

        $yubi = YubikeyData::createYubikey($user, $yubi_pubkey, $yubi_cert, $yubi_cert_issuer, $yubi_cert_subject, $yubi_cred_id, $yubi_rp_id);
        $result = $yubi->create();
        if(!is_bool($result)) {
            die(json_encode(array("error"=>true, "msg"=>"Wystąpił błąd podczas operacji na bazie danych.")));
        }
        else {
            $addedYubi = true;
        }
    }

    die(json_encode(array("error"=>false, "addedOTP"=>$addedOTP, "addedYubi"=>$addedYubi)));
?>