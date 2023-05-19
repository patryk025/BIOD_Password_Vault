<?php
session_start();
require("header.php");

require_once "db/DbAdapter.php";

$email_codes = DbAdapter::queryObjects('email_codes', $_GET['unique_id'], 'identifier');
?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Weryfikacja kodu</h5>
          <?php
          if(count($email_codes) == 0) {
          ?>
          <div class="alert alert-danger fade show" role="alert">
            Identyfikator weryfikacyjny jest niepoprawny.
          </div>
          <?php } else {?>
          <form action="verifyCode.php" method="post">
            <div class="mb-3">
              <label for="verificationCode" class="form-label">Kod weryfikacyjny</label>
              <input type="text" class="form-control" id="verificationCode" name="verification_code" required>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Zweryfikuj kod</button>
            </div>
          </form>
          <?php }?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
require("footer.php");
?>