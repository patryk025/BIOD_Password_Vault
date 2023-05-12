<?php
require("header.php");
?>
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