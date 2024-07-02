<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Połączenie z bazą danych
include_once "config/config.php";

// Sprawdzanie, czy połączenie z bazą danych zostało poprawnie ustanowione
if (!$conn) {
    die("Connection to database failed.");
}

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

// Zmienna do przechowywania komunikatu o dodaniu seansu
$notification = "";

// Dodawanie seansu do bazy danych
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $film = $_POST['film'];
    $sala = $_POST['sala'];
    $data = $_POST['data'];
    $godzina = $_POST['godzina'];

    // Sprawdzanie, czy film i sala są wybrane
    if (empty($film) || empty($sala)) {
        $notification = "Wybierz film i salę.";
    } else {
        // Pobierz aktualną datę i godzinę serwera
        $currentDateTime = new DateTime();
        $seansDateTime = DateTime::createFromFormat('Y-m-d H:i', $data . ' ' . $godzina);

        // Sprawdź, czy wprowadzona data i godzina nie są w przeszłości
        if ($seansDateTime < $currentDateTime) {
            $notification = "Nie można dodać seansu w przeszłości.";
        } else {
            // Sprawdzanie, czy film istnieje w tabeli Filmy
            $filmQuery = $conn->prepare("SELECT ID_filmu FROM ${prefix}Filmy WHERE ID_filmu = ?");
            $filmQuery->bind_param("i", $film);
            $filmQuery->execute();
            $filmResult = $filmQuery->get_result();

            // Sprawdzanie, czy sala istnieje w tabeli Sale
            $salaQuery = $conn->prepare("SELECT ID_sali FROM ${prefix}Sale WHERE ID_sali = ?");
            $salaQuery->bind_param("i", $sala);
            $salaQuery->execute();
            $salaResult = $salaQuery->get_result();

            if ($filmResult->num_rows > 0 && $salaResult->num_rows > 0) {
                // Wstawianie seansu do bazy danych
                $stmt = $conn->prepare("INSERT INTO ${prefix}Seanse (ID_filmu, ID_sali, Data, Godzina) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $film, $sala, $data, $godzina);

                if ($stmt->execute()) {
                    $notification = "Seans został dodany pomyślnie!";
                } else {
                    $notification = "Wystąpił błąd przy dodawaniu seansu: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $notification = "Wybrany film lub sala nie istnieją.";
            }

            $filmQuery->close();
            $salaQuery->close();
        }
    }
}


// Pobieranie listy filmów z bazy danych
$filmyOptions = "<option value=''>Wybierz film</option>";
$query = "SELECT ID_filmu, Tytuł FROM ${prefix}Filmy";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $filmyOptions .= "<option value='" . $row['ID_filmu'] . "'>" . $row['Tytuł'] . "</option>";
}

// Pobieranie listy sal z bazy danych
$saleOptions = "<option value=''>Wybierz salę</option>";
$query = "SELECT ID_sali, Numer_sali FROM ${prefix}Sale";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $saleOptions .= "<option value='" . $row['ID_sali'] . "'>" . $row['Numer_sali'] . "</option>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel admina KKino - Godziny seansów</title>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <a href="wiadomosci.php">
            <i class="fa-solid fa-envelope"></i>
            <i class="txt">Wiadomości</i>
        </a>
        <a href="ADMINgodziny.php" class="current">
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
<main id="pgmain-godziny">
    <div class="form-container">
        <h1>Dodaj Seans</h1>
        <!-- Wyświetlanie powiadomień w JavaScript -->
        <div id="notification" class="notification"><?php echo $notification; ?></div>

        <form action="ADMINgodziny.php" method="post">
            <label for="film">Film:</label>
            <select name="film" id="film">
                <?php echo $filmyOptions; ?>
            </select><br>
            <label for="sala">Sala:</label>
            <select name="sala" id="sala">
                <?php echo $saleOptions; ?>
            </select><br>

            <label for="data">Data:</label>
            <input type="date" id="data" name="data" required><br>

            <label for="godzina">Godzina:</label>
            <input type="time" id="godzina" name="godzina" required><br>

            <input type="submit" name="submit" value="Dodaj Seans">
        </form>
    </div>
</main>



<script>
    // Funkcja do wyświetlania powiadomień
    function showNotification(message) {
        var notification = document.getElementById('notification');
        notification.textContent = message;
        notification.style.display = 'block';

        // Ukrywanie powiadomienia po 3 sekundach
        setTimeout(function() {
            notification.style.display = 'none';
        }, 3000);
    }

    // Wywołanie funkcji showNotification z PHP
    <?php
    if ($notification) {
        echo "showNotification('" . addslashes($notification) . "');";
    }
    ?>
</script>

</body>
</html>
