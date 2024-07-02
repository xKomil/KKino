<?php
session_start();

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

// Dołączenie pliku konfiguracyjnego
include_once "config/config.php";

// Pobieranie nazw biletów z bazy danych
$sql = "SELECT Nazwa_biletu, Cena FROM ${prefix}Bilety"; // Używamy prefiksu ${prefix}
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel admina KKino - Bilety</title>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>" />
    <head>
    <!-- Link do fontawesome klasycznie -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

</head>
<body>
    <!-- (A) SIDEBAR -->
    <div id="pgside">   
        <!-- (A1) BRANDING OR USER -->
        <!-- LINK TO DASHBOARD OR LOGOUT -->
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
    <main id="pgmain-bilety">
        <h1>Zarządzanie cenami biletów</h1>

        <!-- Formularz do zmiany cen biletów -->
        <form action="ADMINbilety.php" method="post">
            <label for="bilet">Wybierz bilet:</label>
            <select name="bilet" id="bilet" required>
                <option value="">Wybierz...</option>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?php echo $row['Nazwa_biletu']; ?>"><?php echo $row['Nazwa_biletu']; ?></option>
                <?php endwhile; ?>
            </select>
            <label for="new_price">Nowa cena (PLN):</label>
            <input type="number" name="new_price" id="new_price" step="0.01" min="10" required>
            <button type="submit" name="submit">Zapisz zmiany</button>
        </form>

        <?php
        // Obsługa formularza
        if (isset($_POST['submit'])) {
            // Pobieranie danych z formularza
            $nazwa_biletu = $_POST['bilet'];
            $nowa_cena = $_POST['new_price'];

            // Aktualizacja ceny biletu w bazie danych
            $sql_update = "UPDATE ${prefix}Bilety SET Cena = ? WHERE Nazwa_biletu = ?";
            $stmt = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt, "ds", $nowa_cena, $nazwa_biletu);
            mysqli_stmt_execute($stmt);

            // Sprawdzamy, czy zapytanie się powiodło
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo "Cena biletu została zaktualizowana.";
            } else {
                echo "Wystąpił błąd podczas aktualizacji ceny biletu lub podałeś cenę większą niż 1000, co jest raczej niespotykane ;).";
            }

            // Zamykamy połączenie
            mysqli_stmt_close($stmt);
        }
        ?>
    </main>

</body>
</html>

