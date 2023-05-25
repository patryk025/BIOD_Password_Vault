<?php

foreach (glob(__DIR__."/models/*.php") as $filename)
{
    require_once $filename;
}

session_start();

$user = $_SESSION['user'];

$key = $user->getPasswords()[0]->generateKey($user);

echo "PHP:";
$method = "AES-256-CBC";
$ivlen = openssl_cipher_iv_length($method);
$iv = openssl_random_pseudo_bytes($ivlen);

$encrypted_base64 = base64_encode("YVoeS/romuOZpZt/RCiluHiDg9tKjQKOZZIJ10JzJko=");

$data = base64_decode($encrypted_base64);

$iv = substr($data, 0, $ivlen);
$encrypted = substr($data, $ivlen);
$decrypted = openssl_decrypt($encrypted, $method, hex2bin($key), OPENSSL_RAW_DATA, $iv);
    
echo "Klucz: $key<br>";
echo "Szyfrowane dane (base64): $encrypted_base64<br>";

echo "Deszyfrowane dane: $decrypted<br>";
?>
<script>
<?php
echo "var key = '$key';\n";
echo "var encrypted = '$encrypted_base64';\n";
?>

function hexToBytes(hex) {
    var bytes = new Uint8Array(Math.ceil(hex.length / 2));
    for (var i = 0; i < bytes.length; i++)
        bytes[i] = parseInt(hex.substr(i * 2, 2), 16);
    return bytes;
}

function bytesToHex(bytes) {
    var hex = [];
    for (var i = 0; i < bytes.length; i++) {
        var current = bytes[i] < 0 ? bytes[i] + 256 : bytes[i];
        hex.push((current >>> 4).toString(16));
        hex.push((current & 0xF).toString(16));
    }
    return hex.join("");
}

function decryptData(encryptedData, key) {
    var encryptedBytes = forge.util.decode64(encryptedData);

    var ivBytes = encryptedBytes.slice(0, 16);
    var cipherBytes = encryptedBytes.slice(16);

    var keyBytes = hexToBytes(key);

    var decipher = forge.cipher.createDecipher('AES-CBC', keyBytes);
    decipher.start({iv: ivBytes});
    decipher.update(forge.util.createBuffer(cipherBytes));
    var result = decipher.finish();

    if (result) {
        return decipher.output.toString();
    } else {
        return null;
    }
}
var decrypted = decryptData(encrypted, key);
document.getElementById("js_data").innerHTML = "Klucz: $key<br>Zaszyfrowane dane (base64): "+encrypted+"<br>Zdeszyfrowane dane:"+decrypted;
</script>
<span id="js_data">

</span>