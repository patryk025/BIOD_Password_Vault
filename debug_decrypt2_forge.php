<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/node-forge@1.0.0/dist/forge.min.js"></script>
<?php
echo "PHP:";
$data = "Hello, World!";
$method = "AES-256-CBC";
$key = bin2hex(openssl_random_pseudo_bytes(32));
$ivlen = openssl_cipher_iv_length($method);
$iv = openssl_random_pseudo_bytes($ivlen);

$encrypted = openssl_encrypt($data, $method, hex2bin($key), OPENSSL_RAW_DATA, $iv);
$encrypted_base64 = base64_encode($iv.$encrypted);

echo "Dane do szyfrowania: $data<br>";
echo "Klucz: $key<br>";
echo "Szyfrowane dane (base64): $encrypted_base64<br>";

$decrypted = openssl_decrypt(substr(base64_decode($encrypted_base64), $ivlen), $method, hex2bin($key), OPENSSL_RAW_DATA, substr(base64_decode($encrypted_base64), 0, $ivlen));

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