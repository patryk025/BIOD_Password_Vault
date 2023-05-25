<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
<span id="js_data">

</span>
<script>
<?php
echo "var key = '$key';\n";
echo "var encrypted = '$encrypted_base64';\n";

?>

function decryptData(encryptedData, key) {
    var rawData = atob(encryptedData);
    var iv = rawData.slice(0, 16);
    var cipherText = rawData.slice(16);
    var WordArrayKey = CryptoJS.enc.Utf8.parse(key);
    var WordArrayIV = CryptoJS.enc.Latin1.parse(iv);
    var WordArrayCipher = CryptoJS.enc.Latin1.parse(cipherText);

    var decrypted = CryptoJS.AES.decrypt({ciphertext: WordArrayCipher}, WordArrayKey, {iv: WordArrayIV});
    return decrypted.toString(CryptoJS.enc.Utf8);
}
var decrypted = decryptData(encrypted, key);
document.getElementById("js_data").innerHTML = "<br>W JS:<br>Klucz: "+key+"<br>Zaszyfrowane dane (base64): "+encrypted+"<br>Zdeszyfrowane dane:"+decrypted;
</script>