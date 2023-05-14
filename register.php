<?php
session_start();
require("header.php");
?>
<script src="scripts/WebAuthnFunctions.js"></script>
<script>
var googleAuthVerified = false;

function switchButtonDiv(elem) {
  if (elem.is(':checked')) {
    $('#2FAMethods').show();
  } else {
    $('#2FAMethods').hide();
  }
}

function verifyCode() {
  var code = $("#authenticatorCode").val();
  $('#authenticatorCode').removeClass('is-invalid');
  $('#authenticatorCode').next('.invalid-feedback').text('');
  if(code == "") {
    $('#authenticatorCode').addClass('is-invalid');
    $('#authenticatorCode').next('.invalid-feedback').text('Kod nie może być pusty, wprowadź kod.');
    return false;
  }
  else if(code.length != 6) {
    $('#authenticatorCode').addClass('is-invalid');
    $('#authenticatorCode').next('.invalid-feedback').text('Niepoprawna długość kodu, musi mieć 6 znaków.');
    return false;
  }
  $.post("api/OtpInterface.php?mode=verify", {code: code}, function(result){
    if(result.valid) {
      $('#authenticatorCode').addClass('is-valid');
      $("#authenticatorModal").modal('hide');
      $('#googleAuthButton').find('.loading-icon').hide();
      $('#googleAuthButton').find('.success-icon').show();
      googleAuthVerified = true;
    }
    else {
      $('#authenticatorCode').addClass('is-invalid');
      $('#authenticatorCode').next('.invalid-feedback').text('Kod jest niepoprawny, spróbuj jeszcze raz.');
      return false;
    }
  });
}

$(document).ready(function() {
  switchButtonDiv($('#enable2FA'));

  $('#enable2FA').change(function() {
    switchButtonDiv($(this));
  });

  $('#googleAuthButton').click(function() {
    // TODO: Implement Google Authenticator pairing
    $(this).prop('disabled', true);
    $(this).find('.loading-icon').show();
    $.get( "api/OtpInterface.php?mode=register", function( data ) {
      $("#authenticatorSecret").val(data.secret);
      $('#qrCodeContainer').html("");
      var img = $('<img>');
      img.attr('src', "data:image/png;base64,"+data.qr_code);
      img.appendTo('#qrCodeContainer');
    });
    var autenticatorModal = new bootstrap.Modal($('#authenticatorModal'), {});
    autenticatorModal.show();

    $('#authenticatorModal').on('hidden.bs.modal', function () {
      if(!googleAuthVerified) {
        $('#googleAuthButton').prop('disabled', false);
        $('#googleAuthButton').find('.loading-icon').hide();
      }
    })
  });

  $('#yubikeyButton').click(function() {
    // TODO: Implement Yubikey pairing
    $(this).prop('disabled', true);
    $(this).find('.loading-icon').show();
    createRegistration();
  });

  $('#verifyCodeButton').click(function() {
    verifyCode();
  });

  $('#authenticatorCode').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
      verifyCode()
    }
  });
});
</script>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Rejestracja</h5>
          <form action="register.php" method="post">
            <div class="mb-3">
              <label for="registerEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="registerEmail" name="email" required>
            </div>
            <div class="mb-3">
              <label for="registerPassword" class="form-label">Hasło</label>
              <input type="password" class="form-control" id="registerPassword" name="password" required>
            </div>
            <div class="mb-3">
              <label for="registerPasswordConfirm" class="form-label">Potwierdź hasło</label>
              <input type="password" class="form-control" id="registerPasswordConfirm" name="password_confirm" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="enable2FA">
                <label class="form-check-label" for="enable2FA">Dodaj uwierzytelnianie dwuskładnikowe</label>
            </div>

            <div class="mb-3 text-center" id="2FAMethods" style="display: none;">
                <button type="button" class="btn btn-primary" id="googleAuthButton" data-toggle="modal" data-target="#authenticatorModal">
                  Google Authenticator
                  <span class="loading-icon" style="display: none;"><i class="fas fa-spinner fa-spin"></i></span>
                  <span class="success-icon" style="display: none;"><i class="fas fa-check-circle"></i></span>
                </button>
                <button type="button" class="btn btn-primary" id="yubikeyButton">
                  Yubikey
                  <span class="loading-icon" style="display: none;"><i class="fas fa-spinner fa-spin"></i></span>
                  <span class="success-icon" style="display: none;"><i class="fas fa-check-circle"></i></span>
                </button>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Zarejestruj się</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="authenticatorModal" tabindex="-1" aria-labelledby="authenticatorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="authenticatorModalLabel">Google Authenticator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="qrCodeContainer" class="mb-3 text-center">
          
        </div>
        <div class="mb-3">
          <label for="authenticatorSecret" class="form-label">lub przepisz sekret, jeśli nie działa skaner QR</label>
          <input type="text" id="authenticatorSecret" class="form-control">
        </div>
        <div class="mb-3">
          <label for="authenticatorCode" class="form-label">Wpisz kod z aplikacji Google Authenticator</label>
          <input type="text" id="authenticatorCode" class="form-control" required>
          <div class="invalid-feedback">
            
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="verifyCodeButton" class="btn btn-primary">Zatwierdź</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
      </div>
    </div>
  </div>
</div>
<?php
require("footer.php");
?>