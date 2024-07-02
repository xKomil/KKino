<?php
session_start();
require_once "config/config.php";

// Zmienna do przechowywania wiadomości
$message = "";

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

// Obsługa dodawania filmu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_movie'])) {
    $tytul = $_POST['tytul'];
    $rezyser = $_POST['rezyser'];
    $gatunek = $_POST['gatunek'];
    $czas_trwania = $_POST['czas_trwania'];
    $opis = $_POST['opis'];
    $zdjecie = $_POST['zdjecie'];
    $zwiastun = $_POST['zwiastun'];
    $rok_powstania = $_POST['rok_powstania'];

    // Walidacja pól
    $errors = [];

    // Walidacja Tytułu
    if (empty($tytul)) {
        $errors[] = "Tytuł nie może być pusty.";
    }

    // Walidacja Reżysera
    if (empty($rezyser)) {
        $errors[] = "Reżyser nie może być pusty.";
    }

    // Walidacja Gatunku
    if (empty($gatunek)) {
        $errors[] = "Gatunek nie może być pusty.";
    }

    // Walidacja Czasu Trwania (musi być liczbą)
    if (!is_numeric($czas_trwania)) {
        $errors[] = "Czas trwania powinien być liczbą.";
    }

    // Walidacja Opisu (opcjonalna, może być pusty)
    // Możesz dodać dodatkowe warunki walidacji w zależności od potrzeb

    // Sprawdzanie błędów
    if (!empty($errors)) {
        $message = "Błąd: " . implode("<br>", $errors);
    } else {
        // Pomyślna walidacja, wykonujemy operację dodawania do bazy danych
        $sql = "INSERT INTO ${prefix}Filmy (Tytuł, Reżyser, Gatunek, Czas_trwania, Opis, Zdjecie, Zwiastun, Rok_powstania) VALUES ('$tytul', '$rezyser', '$gatunek', '$czas_trwania', '$opis', '$zdjecie', '$zwiastun', '$rok_powstania')";
        if (mysqli_query($conn, $sql)) {
            $message = "Film został dodany pomyślnie.";
        } else {
            $message = "Błąd: " . mysqli_error($conn);
        }
    }
}


// Obsługa edytowania filmu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_movie'])) {
    $id = $_POST['id'];
    $tytul = $_POST['tytul'];
    $rezyser = $_POST['rezyser'];
    $gatunek = $_POST['gatunek'];
    $czas_trwania = $_POST['czas_trwania'];
    $opis = $_POST['opis'];
    $zdjecie = $_POST['zdjecie'];
    $zwiastun = $_POST['zwiastun'];
    $rok_powstania = $_POST['rok_powstania'];

    // Walidacja pól
    $errors = [];

    // Walidacja Tytułu
    if (empty($tytul)) {
        $errors[] = "Tytuł nie może być pusty.";
    }

    // Walidacja Reżysera
    if (empty($rezyser)) {
        $errors[] = "Reżyser nie może być pusty.";
    }

    // Walidacja Gatunku
    if (empty($gatunek)) {
        $errors[] = "Gatunek nie może być pusty.";
    }

    // Walidacja Czasu Trwania (musi być liczbą)
    if (!is_numeric($czas_trwania)) {
        $errors[] = "Czas trwania powinien być liczbą.";
    }

    if (!is_numeric($czas_powstania)) {
        $errors[] = "Czas powstania filmu powinien być liczbą.";
    }
    
    // Sprawdzanie błędów
    if (!empty($errors)) {
        $message = "Błąd: " . implode("<br>", $errors);
    } else {
        // Pomyślna walidacja, wykonujemy operację aktualizacji w bazie danych
        $sql = "UPDATE ${prefix}Filmy SET Tytuł='$tytul', Reżyser='$rezyser', Gatunek='$gatunek', Czas_trwania='$czas_trwania', Opis='$opis', Zdjecie='$zdjecie', Zwiastun='$zwiastun', Rok_powstania='$rok_powstania' WHERE ID_filmu='$id'";
        if (mysqli_query($conn, $sql)) {
            $message = "Film został zaktualizowany pomyślnie.";
        } else {
            $message = "Błąd: " . mysqli_error($conn);
        }
    }
}


// Obsługa usuwania filmu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_movie'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM ${prefix}Filmy WHERE ID_filmu='$id'";
    if (mysqli_query($conn, $sql)) {
        $message = "Film został usunięty pomyślnie.";
    } else {
        $message = "Błąd: " . mysqli_error($conn);
    }
}

// Pobieranie filmów z bazy danych
$sql = "SELECT * FROM ${prefix}Filmy";
$result = mysqli_query($conn, $sql);

$filmy = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $filmy[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel admina - Filmy</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>" />
    
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <!-- Link do fontawesome klasycznie -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
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
    <main id="pgmain-filmy">
    <h1>Zarządzanie filmami</h1>
    <div class="message-box" id="message-box"><?php echo htmlspecialchars($message); ?></div>
    <div class="main-add-movie">
        <form method="POST" action="">
            <input type="text" name="tytul" placeholder="Tytuł" required>
            <input type="text" name="rezyser" placeholder="Reżyser" required>
            <input type="text" name="gatunek" placeholder="Gatunek" required>
            <input type="text" name="czas_trwania" placeholder="Czas trwania" required>
            <textarea id="opisid" name="opis" placeholder="Opis" required></textarea>
            <input type="text" name="zdjecie" placeholder="URL do zdjęcia" required>
            <input type="text" name="zwiastun" placeholder="URL do zwiastuna" required>
            <input type="text" name="rok_powstania" placeholder="Rok powstania" required>
            <button type="submit" name="add_movie">Dodaj film</button>
        </form>
    </div>
    <h2>Lista filmów</h2>
    <div class="main-edit-movie">
        <table>
            <thead>
                <tr>
                    <th>Tytuł</th>
                    <th>Reżyser</th>
                    <th>Gatunek</th>
                    <th>Czas trwania</th>
                    <th>Opis</th>
                    <th>Zdjęcie</th>
                    <th>Zwiastun</th>
                    <th>Rok powstania</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filmy as $film): ?>
                <tr>
                    <td><?php echo htmlspecialchars($film['Tytuł']); ?></td>
                    <td><?php echo htmlspecialchars($film['Reżyser']); ?></td>
                    <td><?php echo htmlspecialchars($film['Gatunek']); ?></td>
                    <td><?php echo htmlspecialchars($film['Czas_trwania']); ?></td>
                    <td><?php echo htmlspecialchars($film['Opis']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($film['Zdjecie']); ?>" width="100"></td>
                    <td><a href="<?php echo htmlspecialchars($film['Zwiastun']); ?>" target="_blank">Zwiastun</a></td>
                    <td><?php echo htmlspecialchars($film['Rok_powstania']); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?php echo $film['ID_filmu']; ?>">
                            <button type="submit" name="delete_movie">Usuń</button>
                        </form>
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?php echo $film['ID_filmu']; ?>">
                            <input type="text" name="tytul" placeholder="Tytuł" value="<?php echo htmlspecialchars($film['Tytuł']); ?>" required>
                            <input type="text" name="rezyser" placeholder="Reżyser" value="<?php echo htmlspecialchars($film['Reżyser']); ?>" required>
                            <input type="text" name="gatunek" placeholder="Gatunek" value="<?php echo htmlspecialchars($film['Gatunek']); ?>" required>
                            <input type="text" name="czas_trwania" placeholder="Czas trwania" value="<?php echo htmlspecialchars($film['Czas_trwania']); ?>" required>
                            <textarea id="opisdrugi" name="opis" placeholder="Opis" required><?php echo htmlspecialchars($film['Opis']); ?></textarea>
                            <input type="text" name="zdjecie" placeholder="URL do zdjęcia" value="<?php echo htmlspecialchars($film['Zdjecie']); ?>" required>
                            <input type="text" name="zwiastun" placeholder="URL do zwiastuna" value="<?php echo htmlspecialchars($film['Zwiastun']); ?>" required>
                            <input type="text" name="rok_powstania" placeholder="Rok powstania" value="<?php echo htmlspecialchars($film['Rok_powstania']); ?>" required>
                            <button type="submit" name="edit_movie">Edytuj</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var messageBox = document.getElementById('message-box');
            if (messageBox.textContent.trim() !== "") {
                messageBox.style.display = 'block';
                setTimeout(function() {
                    messageBox.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>

