<?php
foreach (glob(__DIR__."/models/*.php") as $filename)
{
    require_once $filename;
}

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

//CSRF protection
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['token'];

$user = $_SESSION['user'];

if(count($user->getPasswords()) == 0)
    $dec_key = (new Passwords())->generateKey($user);
else
    $dec_key = $user->getPasswords()[0]->generateKey($user);

// Reszta kodu strony
require("header.php");
?>
<style>
    .content-pane {
        display: none;
    }

    .content-pane.active {
        display: block;
    }

    .btn-edit, .btn-delete, .btn-reveal {
        display: inline-block;
        border: none;
        padding: 5px 10px;
        text-decoration: none;
        color: #007bff;
        background-color: #ffffff;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .btn-edit:hover, .btn-delete:hover, .btn-reveal:hover {
        color: #0056b3;
    }
</style>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers : {
                'CsrfToken': '<?php echo $_SESSION['token']; ?>'
            }
        });
        
        $('.list-group-item-action').click(function(e) {
            e.preventDefault();
            $('.active').removeClass('active');
            $(this).addClass('active');
            $('.content-pane').removeClass('active');
            var targetPane = $('#' + $(this).data('target'));
            targetPane.addClass('active');
        });

        $('#password_table').DataTable({
            ajax: {
                url: 'api/passwords.php',
                dataSrc: "passwords",
                error: function(xhr, error, thrown) {
                    console.log('Wystąpił błąd: ' + error);
                }
            },
            columns: [
                { data: 'portal', title: 'Portal' },
                { data: 'login', title: 'Login' },
                { data: 'password', title: 'Hasło', 
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '*'.repeat(8);
                        } else {
                            return data;
                        }
                    } 
                },
                { data: null, defaultContent: '<a href="#" class="btn-edit"><i class="fas fa-edit"></i></a> <a href="#" class="btn-delete"><i class="fas fa-trash-alt"></i></a> <a href="#" class="btn-reveal"><i class="fas fa-eye"></i></a>' }
            ],
            language: {
                processing:     "Przetwarzanie...",
                search:         "Szukaj:",
                lengthMenu:     "Pokaż _MENU_ pozycji",
                info:           "Pozycje od _START_ do _END_ z _TOTAL_ łącznie",
                infoEmpty:      "Pozycji 0 z 0 dostępnych",
                infoFiltered:   "(filtrowanie spośród _MAX_ dostępnych pozycji)",
                infoPostFix:    "",
                loadingRecords: "Wczytywanie...",
                zeroRecords:    "Nie znaleziono pasujących pozycji",
                emptyTable:     "Brak danych",
                paginate: {
                    first:      "Pierwsza",
                    previous:   "Poprzednia",
                    next:       "Następna",
                    last:       "Ostatnia"
                },
                aria: {
                    sortAscending:  ": aktywuj, by posortować kolumnę rosnąco",
                    sortDescending: ": aktywuj, by posortować kolumnę malejąco"
                }
            }
        });
        $('#password_table').on('click', '.btn-reveal', function(e) {
            e.preventDefault();
            var table = $('#password_table').DataTable();
            var tr = $(this).closest('tr');
            var td = tr.find('td').eq(2);
            var row = table.row(tr);
            var data = row.data();

            if ($(this).hasClass('revealed')) {
                td.text('*'.repeat(8));
                $(this).find('svg').removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                td.text(data.password);
                $(this).find('svg').removeClass('fa-eye').addClass('fa-eye-slash');
            }
            $(this).toggleClass('revealed');
        });

        $('#password_table').on('click', '.btn-edit', function(e) {
            e.preventDefault();
            var data = $('#password_table').DataTable().row($(this).parents('tr')).data();
            
            $('#portal').val(data.portal);
            $('#login').val(data.login);
            $('#password').val(data.password);
            
            $('#passwordModalTitle').text('Edytuj hasło');

            $('#passwordModal').modal('show');
        });

        $('#password_table').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var data = $('#password_table').DataTable().row($(this).parents('tr')).data();

            console.log(data);

            $('#deletePasswordModal').data('id', data.id);
            
            $('#deletePasswordModal').modal('show');
        });

        $('#addPassword').on('click', function() {
            $('#passwordForm').trigger('reset');
            $('#passwordModalTitle').text('Dodaj hasło');
            $('#passwordModal').modal('show');
        });

        $('#savePassword').on('click', function() {
            var formData = {
                portal: $('#portal').val(),
                login: $('#login').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'api/passwords.php',
                method: 'POST',
                data: formData,
                success: function() {
                    $('#passwordModal').modal('hide');
                    $('#password_table').DataTable().ajax.reload();
                },
                error: function() {
                    console.error('Wystąpił błąd podczas zapisywania hasła.');
                }
            });
        });

        $('#confirmDelete').on('click', function() {
            var id = $('#deletePasswordModal').data('id');
            
            $.ajax({
                url: 'api/passwords.php?id='+id,
                method: 'DELETE',
                success: function() {
                    $('#deletePasswordModal').modal('hide');
                    $('#password_table').DataTable().ajax.reload();
                },
                error: function() {
                    console.error('Wystąpił błąd podczas usuwania hasła.');
                }
            });
        });

        
        $("#changePasswordForm").submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $("#currentPassword").removeClass('is-invalid');
            $("#newPassword").removeClass('is-invalid');
            $("#confirmPassword").removeClass('is-invalid');

            if($("#newPassword").val() == $("#confirmPassword").val()) {
                $.ajax({
                    type: "POST",
                    url: "api/changePassword.php",
                    data: formData,
                    dataType: "json",
                    success: function(data) {
                        if(data.error) {
                            $('#'+data.obj).addClass('is-invalid');
                            $('#'+data.obj).next('.invalid-feedback').text(data.msg);
                        }
                        else {
                            bootstrap_alert(data.msg, "success");
                        }
                    },
                    error: function(data) {
                        bootstrap_alert("Wystąpił błąd, spróbuj ponownie później", "danger");
                    }
                });
            }
            else {
                $("#newPassword").addClass('is-invalid');
                $("#confirmPassword").addClass('is-invalid');
                $('#confirmPassword').next('.invalid-feedback').text("Podane hasła się nie zgadzają");
            }
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
<style href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"></style>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Logo</a>
            <div>
                <span class="navbar-text text-white"><?php echo $user->getEmail(); ?></span>
                <a href="logout.php" class="btn btn-secondary ms-2">Wyloguj się</a>
            </div>
        </div>
    </nav>
    <div class="container my-4">
        <h1 class="mb-4">Dashboard</h1>
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action active" data-target="content1" aria-current="true">
                        Hasła
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-target="content2">Metody autoryzacji</a>
                    <a href="#" class="list-group-item list-group-item-action" data-target="content3">Ustawienia konta</a>
                </div>
            </div>
            <div class="col-md-9">
                <div id="content1" class="content-pane active">
                    <button type="button" class="btn btn-primary" id="addPassword">Dodaj hasło</button>
                    <table id="password_table" class="display" style="width:100%"></table>
                </div>
                <div id="content2" class="content-pane">Treść dla Metod autoryzacji</div>
                <div id="content3" class="content-pane">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <h2 class="text-center">Zmiana hasła</h2>
                                <form id="changePasswordForm">
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label">Obecne hasło</label>
                                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">Nowe hasło</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Potwierdź nowe hasło</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Zmień hasło</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" id="passwordModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordModalTitle">Dodaj/Edytuj hasło</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="passwordForm">
          <div class="mb-3">
            <label for="portal" class="form-label">Portal</label>
            <input type="text" class="form-control" id="portal" required>
          </div>
          <div class="mb-3">
            <label for="login" class="form-label">Login</label>
            <input type="text" class="form-control" id="login" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Hasło</label>
            <input type="password" class="form-control" id="password" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
        <button type="button" class="btn btn-primary" id="savePassword">Zapisz</button>
      </div>
    </div>
  </div>
</div>
<div class="modal" tabindex="-1" id="deletePasswordModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Usuń hasło</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Czy na pewno chcesz usunąć to hasło?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Usuń</button>
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