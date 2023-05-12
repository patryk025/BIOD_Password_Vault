<?php
require("header.php");
?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Logowanie</h5>
          <form action="login.php" method="post">
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
        </div>
      </div>
    </div>
  </div>
</div>
<?php
require("footer.php");
?>