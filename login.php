<?php
require("header.php");
?>
<script src="scripts/WebAuthnFunctions.js"></script>
<script>
$(document).ready(function() {
  var second_factor = "";
  $("#login_form").submit(function(event) {
    event.preventDefault(); // zapobiega domyślnej akcji formularza

    var formData = $(this).serialize(); // zbiera dane z formularza

    $.ajax({
        type: "POST",
        url: "api/verifyLogin.php",
        data: formData,
        dataType: "json",
        success: function(data) {
          if(data.error)
            bootstrap_alert(data.msg, "danger");
          else {
            switch(data.auth_method) {
              case "yubikey":
                verifyYubikey();
                break;
              case "google_authenticator":
                var autenticatorModal = new bootstrap.Modal($('#authenticatorModal'), {});
                autenticatorModal.show();
                second_factor = "gauth";
                break;
              case "email":
                var autenticatorModal = new bootstrap.Modal($('#authenticatorModal'), {});
                autenticatorModal.show();
                second_factor = "email";
                break;
              default:
                location.href = 'index.php';
            }
          }
        },
        error: function(data) {
          bootstrap_alert("Wystąpił błąd, spróbuj ponownie później", "danger");
        }
    });
  });
});

function verifyYubikey() {
  bootstrap_alert("Not supported yet", "danger");
}

function verifyOTP() {
  var code = $("#twoFactorCode").val();
  $('#twoFactorCode').removeClass('is-invalid');
  $('#twoFactorCode').next('.invalid-feedback').text('');
  if(code == "") {
    $('#twoFactorCode').addClass('is-invalid');
    $('#twoFactorCode').next('.invalid-feedback').text('Kod nie może być pusty, wprowadź kod.');
    return false;
  }
  else if(code.length != 6) {
    $('#twoFactorCode').addClass('is-invalid');
    $('#twoFactorCode').next('.invalid-feedback').text('Niepoprawna długość kodu, musi mieć 6 znaków.');
    return false;
  }
  $.post("api/u2f/OtpInterface.php?mode=verify", {code: code}, function(result){
    if(result.valid) {
      $('#twoFactorCode').addClass('is-valid');
      $("#twoFactorCode").modal('hide');
      location.href = 'index.php';
    }
    else {
      $('#twoFactorCode').addClass('is-invalid');
      $('#twoFactorCode').next('.invalid-feedback').text('Kod jest niepoprawny, spróbuj jeszcze raz.');
      return false;
    }
  });
}

function verifyEmail() {
  var code = $("#twoFactorCode").val();
  $('#twoFactorCode').removeClass('is-invalid');
  $('#twoFactorCode').next('.invalid-feedback').text('');
  if(code == "") {
    $('#twoFactorCode').addClass('is-invalid');
    $('#twoFactorCode').next('.invalid-feedback').text('Kod nie może być pusty, wprowadź kod.');
    return false;
  }
  else if(code.length != 6) {
    $('#twoFactorCode').addClass('is-invalid');
    $('#twoFactorCode').next('.invalid-feedback').text('Niepoprawna długość kodu, musi mieć 6 znaków.');
    return false;
  }
  var formData = $(this).serialize();
  formData += '&email_code=' + encodeURIComponent(code);
  $.ajax({
      type: "POST",
      url: "api/verifyLogin.php",
      data: formData,
      dataType: "json",
      success: function(data) {
        if(data.error) {
          $('#twoFactorCode').addClass('is-invalid');
          $('#twoFactorCode').next('.invalid-feedback').text(data.msg);
        },
        else {
          $('#twoFactorCode').addClass('is-valid');
          $("#twoFactorCode").modal('hide');
          location.href = 'index.php'
        }
      }
      error: function(data) {
        bootstrap_alert("Wystąpił błąd, spróbuj ponownie później", "danger");
      }
  });
}

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
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Logowanie</h5>
          <form id="login_form" action="#" method="post">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="loginEmail" name="email" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Hasło</label>
              <input type="password" class="form-control" id="loginPassword" name="password" required>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Zaloguj się</button>
            </div>
          </form>
          <div class="mb-3">
            <span>Nie masz konta? Zarejestruj się <a href="register.php">tutaj</a></span>
          </div>
        </div>
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
<div class="modal fade" id="twoFactorModal" tabindex="-1" aria-labelledby="twoFactorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="twoFactorModalLabel">Weryfikacja dwuetapowa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="two_factor_form" action="#" method="post">
        <div class="modal-body">
            <div class="mb-3">
              <label for="twoFactorCode" class="form-label">Podaj kod weryfikacyjny</label>
              <input type="text" class="form-control" id="twoFactorCode" name="twoFactorCode" required>
              <div class="invalid-feedback">
                
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
          <button type="submit" class="btn btn-primary" id="verifyCodeButton">Zweryfikuj</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
require("footer.php");
?>