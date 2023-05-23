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
</style>
<script>
    $(document).ready(function() {
        $('.list-group-item-action').click(function(e) {
            e.preventDefault();
            $('.content-pane').removeClass('active');
            var targetPane = $('#' + $(this).data('target'));
            targetPane.addClass('active');
        });
    });
</script>
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
                <div id="content1" class="content-pane active">Treść dla Hasła</div>
                <div id="content2" class="content-pane">Treść dla Metod autoryzacji</div>
                <div id="content3" class="content-pane">Treść dla Ustawień konta</div>
            </div>
        </div>
    </div>
<?php
require("footer.php");
?>