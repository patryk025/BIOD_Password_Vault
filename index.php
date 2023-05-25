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
    function stringToUint8Array(string) {
        var stringBytes = new Uint8Array(string.length / 2);
        for (var i = 0; i < string.length; i += 2) {
            stringBytes[i / 2] = parseInt(string.substring(i, i + 2), 16);
        }
        return stringBytes;
    }

    function base64ToUint8Array(base64) {
        const binaryString = atob(base64);
        const bytes = new Uint8Array(binaryString.length);
        for (let i = 0; i < binaryString.length; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes;
    }

    async function decryptData(data, key) {
        const dataBytes = base64ToUint8Array(data);
        let iv = dataBytes.slice(0, 16);
        const encryptedBytes = dataBytes.slice(16);

        var keyBytes = stringToUint8Array(key);

        console.log("keyBytes", keyBytes);
        console.log("encryptedBytes", encryptedBytes);
        console.log("iv", iv);
        
        try {
            const decodedKey = await window.crypto.subtle.importKey(
                "raw", 
                keyBytes.buffer, 
                "AES-CBC", 
                false, 
                ["decrypt"]
            );

            console.log("decodedKey", decodedKey);

            const decrypted = await window.crypto.subtle.decrypt(
                { name: "AES-CBC", iv: iv.buffer },
                decodedKey, 
                encryptedBytes.buffer
            );

            return new TextDecoder().decode(decrypted);
        } catch (error) {
            console.error("Błąd podczas dekodowania danych:", error);
        }

        return data;
    }

    var key = "<?php echo $dec_key; ?>"; //TODO: przyznaje, jest to dość mocno niebezpieczne zagnieżdżać klucz w zmiennej.

    $(document).ready(function() {
        $.ajaxSetup({
            headers : {
                'CsrfToken': '<?php echo $_SESSION['token']; ?>'
            }
        });
        
        $('.list-group-item-action').click(function(e) {
            e.preventDefault();
            $('.content-pane').removeClass('active');
            var targetPane = $('#' + $(this).data('target'));
            targetPane.addClass('active');
        });

        $.ajax({
            url: 'api/passwords.php',
            method: 'GET',
            dataType: 'json',
            success: function(json) {
                let decryptionPromises = json.passwords.map(password => {
                    return $.when(
                        decryptData(password.portal, key),
                        decryptData(password.login, key),
                        decryptData(password.password, key)
                    ).then(function(decryptedPortal, decryptedLogin, decryptedPassword) {
                        return {
                            ...password,
                            portal: decryptedPortal,
                            login: decryptedLogin,
                            password: decryptedPassword
                        };
                    });
                });

                $.when(...decryptionPromises).then(function(...decryptedPasswords) {
                    json.passwords = decryptedPasswords;
                    
                    // teraz możemy inicjować DataTables
                    $('#password_table').DataTable({
                        data: json.passwords,
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
                });
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
    });
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
                <div id="content3" class="content-pane">Treść dla Ustawień konta</div>
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
<?php
require("footer.php");
?>