<?php
session_start();
require_once "config/config.php";

// Sprawdzenie, czy użytkownik jest zalogowany i czy jest administratorem
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Obsługa wylogowania
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] == true) {
    session_destroy();
    header("Location: logowanie.php");
    exit();
}

// Obsługa usuwania wiadomości
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_message'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM ${prefix}Wiadomosci WHERE ID_wiadomosci='$id'";
    if (mysqli_query($conn, $sql)) {
        $message = "Wiadomość została usunięta pomyślnie.";
    } else {
        $message = "Błąd: " . mysqli_error($conn);
    }
}

// Pobieranie wiadomości z bazy danych
$sql = "SELECT * FROM ${prefix}Wiadomosci";
$result = mysqli_query($conn, $sql);

$wiadomosci = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $wiadomosci[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel admina KKino - Wiadomości</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>" />
   
</head>
<body>
    <!-- (A) SIDEBAR -->
    <div id="pgside">   
        <!-- (A1) BRANDING OR USER -->
        <div id="pguser">
            <img src="assets/KK Cinema.ico">
            <i class="txt">Panel admina</i>
        </div>

        <!-- (A2) MENU ITEMS -->
        <a href="admin.php">
            <i class="fa-solid fa-user"></i>
            <i class="txt">Użytkownicy</i>
        </a>
        <a href="ADMINfilmy.php">
            <i class="fa-solid fa-film"></i>
            <i class="txt">Filmy</i>
        </a>
        <a href="ADMINbilety.php">
            <i class="fa-solid fa-ticket"></i>
            <i class="txt">Bilety</i>
        </a>
        <a href="wiadomosci.php" class="current">
            <i class="fa-solid fa-envelope"></i>
            <i class="txt">Wiadomości</i>
        </a>
        <a href="ADMINgodziny.php">
            <i class="fa-solid fa-clock"></i>
            <i class="txt">Godziny</i>
        </a>

        <!-- (A3) LOGOUT BUTTON -->
        <form action="" method="post">
            <input type="hidden" name="logout" value="true">
            <button type="submit">
                <i class="fa-solid fa-right-from-bracket"></i>
                <i class="txt">Wyloguj</i>
            </button>
        </form>
    </div>

    <!-- (B) MAIN -->
    <main id="pgmain-wiadomosci">
        <h1>Wiadomości</h1>
        <?php if (!empty($message)) : ?>
            <div class="message-box"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="message-container">
            <?php foreach ($wiadomosci as $wiadomosc) : ?>
                <div class="message">
                    <div class="message-header">
                        <span class="message-user"><?php echo htmlspecialchars($wiadomosc['Uzytkownik']); ?></span>
                        <span class="message-topic"><?php echo htmlspecialchars($wiadomosc['Temat']); ?></span>
                    </div>
                    <div class="message-body">
                        <?php echo htmlspecialchars($wiadomosc['Wiadomosc']); ?>
                    </div>
                    <div class="message-actions">
                        <form method="post" action="">
                            <input type="hidden" name="id" value="<?php echo $wiadomosc['ID_wiadomosci']; ?>">
                            <button type="submit" name="delete_message">Usuń</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
