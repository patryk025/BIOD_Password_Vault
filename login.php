<?php
require("header.php");
?>
<script>
$(document).ready(function() {
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
          else
            location.href = 'index.php';
        },
        error: function(data) {
          bootstrap_alert("Wystąpił błąd, spróbuj ponownie później", "danger");
        }
    });
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
<?php
require("footer.php");
?>