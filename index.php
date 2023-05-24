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
        color: #ffffff;
        background-color: #007bff;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .btn-edit:hover, .btn-delete:hover, .btn-reveal:hover {
        background-color: #0056b3;
    }
</style>
<script>
    async function decryptData(data, key) {
        const iv = data.slice(0, 16);
        const encrypted = data.slice(16);
        
        const decodedKey = await window.crypto.subtle.importKey(
            "raw", 
            key, 
            "AES-CBC", 
            false, 
            ["decrypt"]
        );

        const decrypted = await window.crypto.subtle.decrypt(
            { name: "AES-CBC", iv: iv },
            decodedKey, 
            encrypted
        );

        return new TextDecoder().decode(decrypted);
    }

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
        $('#password_table').DataTable({
            ajax: {
                url: 'api/passwords.php',
                dataSrc: 'passwords',
                error: function(xhr, error, thrown) {
                    console.log('Wystąpił błąd: ' + error);
                }
            },
            columns: [
                { data: 'portal', title: 'Portal' },
                { data: 'login', title: 'Login' },
                { data: 'password', title: 'Hasło' },
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
        $(".btn-edit, .btn-delete, .btn-reveal").click(function(e) {
            e.preventDefault();
            // tutaj kod, który ma zostać wykonany po kliknięciu
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
                    <table id="password_table" class="display" style="width:100%"></table>
                </div>
                <div id="content2" class="content-pane">Treść dla Metod autoryzacji</div>
                <div id="content3" class="content-pane">Treść dla Ustawień konta</div>
            </div>
        </div>
    </div>
<?php
require("footer.php");
?>