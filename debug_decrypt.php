<script>
<?php

foreach (glob(__DIR__."/models/*.php") as $filename)
{
    require_once $filename;
}

session_start();

$user = $_SESSION['user'];

$key = $user->getPasswords()[0]->generateKey($user);

$text = "TEST";

print("var text = \"$text\";\n");
print("var key = \"$key\";\n");

$method = "AES-256-CBC";
$ivlen = openssl_cipher_iv_length($method);
$iv = openssl_random_pseudo_bytes($ivlen);
$encrypted = openssl_encrypt($text, $method, $key, OPENSSL_RAW_DATA, $iv);
$base64_encrypt = base64_encode($iv.$encrypted);

print("var encrypted = \"$base64_encrypt\";\n");

$data = base64_decode($base64_encrypt);
$ivlen = openssl_cipher_iv_length($method);
$iv = substr($data, 0, $ivlen);
$encrypted = substr($data, $ivlen);
$decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);

print("var decrypted = \"$decrypted\";\n");
?>
function stringToUint8Array(string) {
    var stringBytes = new Uint8Array(string.length / 2);
    for (var i = 0; i < string.length; i += 2) {
        stringBytes[i / 2] = parseInt(string.substring(i, i + 2), 16);
    }
    return stringBytes;
}

function base64ToUint8Array(base64) {
    const binaryString = atob(base64);
    const bytes = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes;
}

async function decryptData(data, key) {
    const dataBytes = base64ToUint8Array(data);
    let iv = dataBytes.slice(0, 16);
    const encryptedBytes = dataBytes.slice(16);

    var keyBytes = stringToUint8Array(key);

    console.log("keyBytes", keyBytes);
    console.log("encryptedBytes", encryptedBytes);
    console.log("iv", iv);
    
    try {
        const decodedKey = await window.crypto.subtle.importKey(
            "raw", 
            keyBytes.buffer, 
            "AES-CBC", 
            false, 
            ["decrypt"]
        );

        console.log("decodedKey", decodedKey);

        const decrypted = await window.crypto.subtle.decrypt(
            { name: "AES-CBC", iv: iv.buffer },
            decodedKey, 
            encryptedBytes.buffer
        );

        console.log(new TextDecoder().decode(decrypted));
    } catch (error) {
        console.error("Błąd podczas dekodowania danych:", error);
    }
}

decryptData(encrypted, key);
</script>