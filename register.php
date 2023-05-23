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
  $.post("api/u2f/OtpInterface.php?mode=verify", {code: code}, function(result){
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
    $.get( "api/u2f/OtpInterface.php?mode=register", function( data ) {
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
    $(this).prop('disabled', true);
    $(this).find('.loading-icon').show();
    createRegistration().then(function(response) {
      if(response.error) {
        $('#yubikeyButton').find('.loading-icon').hide();
        $('#yubikeyButton').prop('disabled', false);
        switch(response.status) {
          case "reg_canceled": {
            bootstrap_alert("Anulowano parowanie z Yubikeyem", "info");
            break;
          }
          case "reg_error": {
            bootstrap_alert("Wystąpił błąd: "+response.message, "danger");
          }
        }
      }
      else {
        if(response.status == "reg_complete") {
          $('#yubikeyButton').find('.loading-icon').hide();
          $('#yubikeyButton').find('.success-icon').show();
        }
      }
    })
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

  $("#register_form").submit(function(event) {
    event.preventDefault(); // zapobiega domyślnej akcji formularza

    var emailIsValid = $("#registerEmail").hasClass('is-valid');
    var passwordIsValid = $("#registerPassword").hasClass('is-valid');
    var confirmPasswordIsValid = $("#registerPasswordConfirm").hasClass('is-valid');

    // sprawdzanie, czy wszystkie pola są poprawne
    if (!(emailIsValid && passwordIsValid && confirmPasswordIsValid)) {
      bootstrap_alert("Proszę poprawić błędy w formularzu przed wysłaniem", "warning");
    }
    else {
      var formData = $(this).serialize(); // zbiera dane z formularza

      $.ajax({
          type: "POST",
          url: "api/saveNewUser.php",
          data: formData,
          dataType: "json",
          success: function(data) {
            if(data.error)
              bootstrap_alert(data.msg, "danger");
            else
              bootstrap_alert("Na podanego maila wysłano kod jednorazowy. Proszę postępować zgodnie z instrukcjami", "success");
          },
          error: function(data) {
            bootstrap_alert("Wystąpił błąd, spróbuj ponownie później", "danger");
          }
      });
    }
  });
  $("#registerPassword, #registerPasswordConfirm").on('input', function(e) {
    var password = $("#registerPassword").val();
    var confirmPassword = $("#registerPasswordConfirm").val();
    $("#registerPassword").removeClass('is-valid');
    if (password !== confirmPassword || password == "") {
      $("#registerPasswordConfirm").addClass('is-invalid');
      $("#registerPasswordConfirm").removeClass('is-valid');
      $("#registerPassword").removeClass('is-valid');
      $(".password_error").text("Hasła nie są takie same");
    } else {
      $("#registerPasswordConfirm").removeClass('is-invalid');
      $("#registerPassword").addClass('is-valid');
      $("#registerPasswordConfirm").addClass('is-valid');
      $(".password_error").text("");
    }
  });
  $("#registerEmail").on('input', function(e) {
    var email = $("#registerEmail").val();
    // RegEx pattern for email validation
    var emailPattern = /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!emailPattern.test(email)) {
      $("#registerEmail").addClass('is-invalid'); //Bootstrap 4 invalid class
      $("#registerEmail").removeClass('is-valid');
      $(".email_error").text("Niepoprawny adres email"); //Display error message
    } else {
      $("#registerEmail").removeClass('is-invalid');
      $("#registerEmail").addClass('is-valid');
      $(".email_error").text(""); //Clear error message
    }
  });
  $("#registerPassword").on("keyup", function() {
    var password = $(this).val();
    var result = zxcvbn(password);
    
    switch(result.score) {
      case 0:
        $("#passwordStrengthInfo").text("Hasło bardzo słabe");
        break;
      case 1:
        $("#passwordStrengthInfo").text("Hasło słabe");
        break;
      case 2:
        $("#passwordStrengthInfo").text("Hasło średnie");
        break;
      case 3:
        $("#passwordStrengthInfo").text("Hasło silne");
        break;
      case 4:
        $("#passwordStrengthInfo").text("Hasło bardzo silne");
        break;
    }
    
    // Zaktualizuj pasek postępu siły hasła
    var strengthPercentage = result.score * 25;
    $("#passwordStrengthIndicator").css("width", strengthPercentage + "%").attr("aria-valuenow", strengthPercentage);
  });
});

function bootstrap_alert(message, alertType = "warning") {
  message = `
      <div class="alert alert-${alertType} fade show" role="alert">
          ${message}
      </div>
  `;
  $('#alertModal').find('.modal-body').html(message);
  $('#alertModal').modal('show');
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Rejestracja</h5>
          <form id="register_form" action="#" method="post">
            <div class="mb-3">
              <label for="registerEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="registerEmail" name="email" required>
              <span class="email_error text-danger"></span>
            </div>
            <div class="mb-3">
              <label for="registerPassword" class="form-label">Hasło</label>
              <input type="password" class="form-control" id="registerPassword" name="password" required>
            </div>
            <div class="mb-3">
              <label for="registerPasswordConfirm" class="form-label">Potwierdź hasło</label>
              <input type="password" class="form-control" id="registerPasswordConfirm" name="password_confirm" required>
              <span class="password_error text-danger"></span>
            </div>
            <div class="mb-3">
              <span>Siła hasła:</span>
            </div>
            <div class="mb-3 progress">
              <div id="passwordStrengthIndicator" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="mb-3">
              <div id="passwordStrengthInfo"></div>
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
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Komunikat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<?php
require("footer.php");
?>