async function createRegistration() {
    try {

        // check browser support
        if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
            return {error: true, status: "reg_error", message: 'Browser not supported.'};
        }

        // get create args
        let rep = await window.fetch('api/u2f/WebAuthnServer.php?fn=getCreateArgs' + getGetParams(), {method:'GET', cache:'no-cache'});
        const createArgs = await rep.json();

        // error handling
        if (createArgs.success === false) {
            return {error: true, status: "reg_error", message: createArgs.msg || 'unknown error occured'};
        }

        // replace binary base64 data with ArrayBuffer. a other way to do this
        // is the reviver function of JSON.parse()
        recursiveBase64StrToArrayBuffer(createArgs);

        // create credentials
        try {
            const cred = await navigator.credentials.create(createArgs);
            
            // create object
            const attestationResponse = {
                transports: cred.response.getTransports  ? cred.response.getTransports() : null,
                clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
            };

            // check auth on server side
            rep = await window.fetch('api/u2f/WebAuthnServer.php?fn=processCreate' + getGetParams(), {
                method  : 'POST',
                body    : JSON.stringify(attestationResponse),
                cache   : 'no-cache'
            });
            const authenticatorServerResponse = await rep.json();

            // prompt server response
            if (authenticatorServerResponse.success) {
                return {error: false, status: "reg_complete"};
            } else {
                return {error: true, status: "reg_error", message: authenticatorServerResponse.msg};
            }
        } catch (error) {
            console.log(error);
            if (error.name === "NotAllowedError") {
                return {error: true, status: "reg_canceled"};
            }
        }
    } catch (err) {
        return {error: true, status: "reg_error", message: err.message || 'unknown error occured'};
    }
}


/**
 * checks a FIDO2 registration
 * @returns {undefined}
 */
async function checkRegistration(email) {
    try {

        if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
            return {error: true, status: "check_error", message: 'Browser not supported.'};
        }

        // get check args
        let rep = await window.fetch('api/u2f/WebAuthnServer.php?fn=getGetArgs&email=' + email + getGetParams(), {method:'GET',cache:'no-cache'});
        const getArgs = await rep.json();

        // error handling
        if (getArgs.success === false) {
            return {error: true, status: "check_error", message: getArgs.msg};
        }

        // replace binary base64 data with ArrayBuffer. a other way to do this
        // is the reviver function of JSON.parse()
        recursiveBase64StrToArrayBuffer(getArgs);

        // check credentials with hardware
        try {
            const cred = await navigator.credentials.get(getArgs);

            // create object for transmission to server
            const attestationResponse = {
                id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
                clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
                signature: cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null,
                userHandle: cred.response.userHandle ? arrayBufferToBase64(cred.response.userHandle) : null
            };

            // send to server
            rep = await window.fetch('api/u2f/WebAuthnServer.php?fn=processGet' + getGetParams(), {
                method:'POST',
                body: JSON.stringify(attestationResponse),
                cache:'no-cache'
            });
            const authenticatorServerResponse = await rep.json();

            // check server response
            if (authenticatorServerResponse.success) {
                return {error: false, status: "check_complete"};
            } else {
                return {error: true, status: "check_error", message: authenticatorServerResponse.msg};
            }
        } catch (error) {
            console.log(error);
            if (error.name === "NotAllowedError") {
                return {error: true, status: "check_canceled"};
            }
        }

    } catch (err) {
        return {error: true, status: "check_error", message: err.message || 'unknown error occured'};
    }
}

function clearRegistration() {
    window.fetch('api/u2f/WebAuthnServer.php?fn=clearRegistrations' + getGetParams(), {method:'GET',cache:'no-cache'}).then(function(response) {
        return response.json();

    }).then(function(json) {
       if (json.success) {
           return {error: false, status: "clear_complete"};
       } else {
           return {error: true, status: "clear_error", message: json.msg};
       }
    }).catch(function(err) {
        return {error: true, status: "clear_error", message: err.message || 'unknown error occured'};
    });
}

function recursiveBase64StrToArrayBuffer(obj) {
    let prefix = '=?BINARY?B?';
    let suffix = '?=';
    if (typeof obj === 'object') {
        for (let key in obj) {
            if (typeof obj[key] === 'string') {
                let str = obj[key];
                if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                    str = str.substring(prefix.length, str.length - suffix.length);

                    let binary_string = window.atob(str);
                    let len = binary_string.length;
                    let bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++)        {
                        bytes[i] = binary_string.charCodeAt(i);
                    }
                    obj[key] = bytes.buffer;
                }
            } else {
                recursiveBase64StrToArrayBuffer(obj[key]);
            }
        }
    }
}

function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa(binary);
}

function getGetParams() {
    return "&apple=0&yubico=0&solo=0&hypersecu=0&google=0&microsoft=0&mds=0&requireResidentKey=0&type_usb=1&type_nfc=1&type_ble=1&type_int=1&type_hybrid=1&fmt_android-key=1&fmt_android-safetynet=1&fmt_apple=1&fmt_fido-u2f=1&fmt_none=0&fmt_packed=1&fmt_tpm=1&rpId=&userId=00&userName=PasswordVault&userDisplayName=Password%20Vault&userVerification=discouraged";
}
