<?php
require("header.php");
?>
<script>
$(document).ready(function() {
  if ($('#enable2FA').is(':checked')) {
    $('#2FAMethods').show();
  } else {
    $('#2FAMethods').hide();
  }

  $('#enable2FA').change(function() {
    if ($(this).is(':checked')) {
      $('#2FAMethods').show();
    } else {
      $('#2FAMethods').hide();
    }
  });

  $('#googleAuthButton').click(function() {
    // TODO: Implement Google Authenticator pairing
    $(this).prop('disabled', true);
    $(this).after('<span class="ms-2"><i class="fas fa-spinner fa-spin"></i></span>');
    //$(this).after('<span class="text-success ms-2"><i class="fas fa-check-circle"></i></span>');
  });

  $('#yubikeyButton').click(function() {
    // TODO: Implement Yubikey pairing
    $(this).prop('disabled', true);
    $(this).after('<span class="ms-2"><i class="fas fa-spinner fa-spin"></i></span>');
    //$(this).after('<span class="text-success ms-2"><i class="fas fa-check-circle"></i></span>');
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
                <button type="button" class="btn btn-primary" id="googleAuthButton">Google Authenticator</button>
                <button type="button" class="btn btn-primary" id="yubikeyButton">Yubikey</button>
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
<?php
require("footer.php");
?>