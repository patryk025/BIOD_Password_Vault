<?php
session_start();
require("header.php");

require_once __DIR__."/db/DbAdapter.php";
require_once __DIR__."/api/mailer/sendEmail.php";

$email_codes = DbAdapter::queryObjects('email_codes', $_GET['unique_id'], 'identifier');
$email_codes = array_reverse($email_codes);
$success = false;
?>
<script>
function resendCode() {
  location.href= "verify_email.php?unique_id=<?php echo $_GET["unique_id"];?>&resend";
}
</script>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Weryfikacja kodu</h5>
          <?php
          $valid = count($email_codes) >= 1;
          if($valid) {
            $email_code = $email_codes[0];
            $identifier = $email_code->getIdentifier();
            if($_GET['unique_id'] != $identifier) $valid = !$valid;
          }
          if(isset($_GET['resend'])) {
            $user = DbAdapter::queryObject('users', $email_code->getIdUser());
            sendOneTimeCode($user, $_GET['unique_id']);

            $email_codes = DbAdapter::queryObjects('email_codes', $_GET['unique_id'], 'identifier');
            $email_codes = array_reverse($email_codes);

            $valid = count($email_codes) >= 1;
            if($valid) {
              $email_code = $email_codes[0];
              $identifier = $email_code->getIdentifier();
              if($_GET['unique_id'] != $identifier) $valid = !$valid;
            }
            ?>
            <div class="alert alert-info fade show" role="alert">
              Kod został wysłany ponownie. Sprawdź skrzynkę i wprowadź kod poniżej
            </div>
          <?php }
          if(!$valid) {
          ?>
          <div class="alert alert-danger fade show" role="alert">
            Identyfikator weryfikacyjny jest niepoprawny.
          </div>
          <?php } 
          else if(strtotime($email_code->getValidTo()) < time()) { ?>
          <div class="alert alert-danger fade show" role="alert">
            Identyfikator weryfikacyjny wygasł.
          </div>
          <div class="d-grid gap-2">
            <button onclick="resendCode()" class="btn btn-primary">Wyślij ponownie kod autoryzujący</button>
          </div>
          <?php }
          else {
            if(isset($_POST['verification_code'])) {
              if($_POST['verification_code'] == $email_code->getValidCode()) {
                $user = DbAdapter::queryObject('users', $email_code->getIdUser());
                $user->verifyUser();
                DbAdapter::removeObject('email_codes', $_GET['unique_id'], 'identifier');
                ?>
                <div class="alert alert-success fade show" role="alert">
                  Konto zostało potwierdzone, można się już logować za jego pomocą.
                </div>
                <div class="mb-3">
                  <span>Przejdź do <a href="login.php">logowania</a></span>
                </div>
              <?php 
                $success = true;
              } else { ?>
                <div class="alert alert-danger fade show" role="alert">
                  Kod jest niepoprawny. Spróbuj jeszcze raz
                </div>
          <?php 
              }
            } 
            if(!$success) { ?>
          <form action="verify_email.php?unique_id=<?php echo $_GET["unique_id"] ?>" method="post">
            <div class="mb-3">
              <label for="verificationCode" class="form-label">Kod weryfikacyjny</label>
              <input type="text" class="form-control" id="verificationCode" name="verification_code" required>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Zweryfikuj kod</button>
            </div>
          </form>
          <?php 
            } 
          }?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
require("footer.php");
?>