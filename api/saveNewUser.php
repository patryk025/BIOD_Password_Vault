<?php
    session_start();

    $email = $_POST['email'];
    $password = $_POST['pass'];

    if(isset($_SESSION['otp_verified']) && $_SESSION['otp_verified']) {
        $otp_secret = $_SESSION['otp_secret'];
    }

    if(isset($_SESSION['registrations'])) {
        $yubi_data = $_SESSION['registrations'][0];

        $yubi_pubkey = $yubi_data['credentialPublicKey'];
        $yubi_cert = $yubi_data['certificate'];
        $yubi_cert_issuer = $yubi_data['certificateIssuer'];
        $yubi_cert_subject = $yubi_data['certificateSubject'];
    }

?>