<?php
require("header.php");
require_once __DIR__."/db/DbAdapter.php";
require_once __DIR__."/api/mailer/sendEmail.php";

session_start();

if (isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

session_unset();

$user = null;
$reset_prompt = null;
$valid = false;
$passwordChanged = false;

if(isset($_GET['unique_id'])) {
    $reset_prompts = DbAdapter::queryObjects('password_reset_prompt', $_GET['unique_id'], 'identifier');
    $reset_prompts = array_reverse($reset_prompts);
    
    if(!empty($reset_prompts)) {
        $reset_prompt = $reset_prompts[0];
        $user = DbAdapter::queryObject('users', $reset_prompt->getIdUser());
        
        if($_GET['unique_id'] == $reset_prompt->getIdentifier() && strtotime($reset_prompt->getValidTo()) > time()) {
            $valid = true;
        }

        if(isset($_POST['newPassword']) && isset($_POST['confirmPassword'])) {
            $password = $_POST['newPassword'];
            $confirmPassword = $_POST['confirmPassword'];
            
            if($password == $confirmPassword) {
                $user = DbAdapter::queryObject('users', $reset_prompt->getIdUser());
                $user->setPassword($password);
                $user->update();
                $reset_prompt->deactivate();
                $reset_prompt->update();
                $passwordChanged = true;
            }
        }
    }
} else if(isset($_POST['email'])) {
    $user = DbAdapter::queryObject('users', $_POST['email'], 'email');
    
    if($user) {
        sendOneTimeCode($user, null, "remember_password");
    }
}
?>
<script>
$(document).ready(function() {
    $("#change_password").submit(function(event) {
        var changingPassword = <?php echo isset($_GET['unique_id']) ? "true" : "false"; ?>;

        if(changingPassword) {
            if($("#newPassword").val() != $("#confirmPassword").val() || $("#newPassword").val() == "") {
                event.preventDefault(); //zatrzymaj formatkę
            }
        }
    });

    $("#newPassword, #confirmPassword").on('input', function(e) {
        var password = $("#newPassword").val();
        var confirmPassword = $("#confirmPassword").val();
        $("#newPassword").removeClass('is-valid');
        if (password !== confirmPassword || password == "") {
            $("#confirmPassword").addClass('is-invalid');
            $("#confirmPassword").removeClass('is-valid');
            $("#newPassword").removeClass('is-valid');
            $(".password_error").text("Hasła nie są takie same");
        } else {
            $("#confirmPassword").removeClass('is-invalid');
            $("#newPassword").addClass('is-valid');
            $("#confirmPassword").addClass('is-valid');
            $(".password_error").text("");
        }
    });

    $("#newPassword").on("keyup", function() {
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
          <h5 class="card-title text-center">Formularz zmiany hasła</h5>
          <?php if ($valid): ?>
            <?php if(!$passwordChanged): ?>
                <form id="change_password" action="#" method="post">
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Nowe hasło</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Powtórz nowe hasło</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
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
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Zatwierdź</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-success fade show" role="alert">
                    Hasło zostało zmienione.
                </div>
            <?php endif; ?>
          <?php else: ?>
            <?php if(isset($_POST['email'])): ?>
                <div class="alert alert-success fade show" role="alert">
                    Jeżeli podano poprawny adres e-mail to wysłano wiadomość e-mail z linkiem do resetu hasła.
                </div>
            <?php endif; ?>
            <?php if(strtotime($reset_prompt->getValidTo()) < time()): ?>
                <div class="alert alert-danger fade show" role="alert">
                    Podany link wygasł. Wygeneruj nowy.
                </div>
            <?php else: ?>
                <form id="change_password" action="#" method="post">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Przypomnij hasło</button>
                    </div>
                </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
