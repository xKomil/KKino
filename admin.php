<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Sprawdzenie, czy użytkownik jest zalogowany i czy jest administratorem
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Dołączenie pliku z połączeniem do bazy danych
include_once "config/config.php"; // Załóżmy, że tutaj masz zdefiniowane zmienne, w tym $conn dla połączenia

// Obsługa wylogowania
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] == true) {
    session_destroy();
    header("Location: logowanie.php");
    exit();
}

// Obsługa usuwania użytkownika
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM ${prefix}Użytkownicy WHERE ID_użytkownika = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Pobieranie danych użytkownika do edycji
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM ${prefix}Użytkownicy WHERE ID_użytkownika = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

// Aktualizacja danych użytkownika
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $rola = $_POST['rola'];

    $stmt = $conn->prepare("UPDATE ${prefix}Użytkownicy SET Imię = ?, Nazwisko = ?, Email = ?, Rola = ? WHERE ID_użytkownika = ?");
    $stmt->bind_param("ssssi", $imie, $nazwisko, $email, $rola, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}

// Pobieranie użytkowników z bazy danych
$result = $conn->query("SELECT ID_użytkownika, Imię, Nazwisko, Email, Rola FROM ${prefix}Użytkownicy");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel admina KKino</title>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>" />
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
    <main id="pgmain">
        <section class="user-management">
            <div class="container-usermanagement">
                <h1 class="h1 section-title-usermanagement">Zarządzanie Użytkownikami</h1>
                
                <!-- Wyszukiwarka użytkowników -->
                <div class="search-container">
                    <input type="text" id="search-input" placeholder="Szukaj użytkowników..." onkeyup="searchUsers()">
                </div>
                
                <table class="user-table" id="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['ID_użytkownika']); ?></td>
                                <td><?php echo htmlspecialchars($user['Imię']); ?></td>
                                <td><?php echo htmlspecialchars($user['Nazwisko']); ?></td>
                                <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                <td><?php echo htmlspecialchars($user['Rola']); ?></td>
                                <td>
                                    <a href="admin.php?edit_id=<?php echo $user['ID_użytkownika']; ?>" class="btn btn-primary-usermanagement">Edytuj</a>
                                    <a href="admin.php?delete_id=<?php echo $user['ID_użytkownika']; ?>" class="btn btn-danger-usermanagement" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Formularz edycji użytkownika -->
                <?php if ($edit_user): ?>
                    <h2 id="napis" class="h2 section-title-usermanagement">Edytuj Użytkownika</h2>
                    <form action="" method="post" id="edit-form" <?php if (!isset($_GET['edit_id'])) echo 'style="display: none;"'; ?>>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_user['ID_użytkownika']); ?>">
                        <div>
                            <label for="imie">Imię:</label>
                            <input type="text" id="imie" name="imie" value="<?php echo htmlspecialchars($edit_user['Imię']); ?>" required>
                        </div>
                        <div>
                            <label for="nazwisko">Nazwisko:</label>
                            <input type="text" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($edit_user['Nazwisko']); ?>" required>
                        </div>
                        <div>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['Email']); ?>" required>
                        </div>
                        <div>
                            <label for="rola">Rola:</label>
                            <select id="rola" name="rola" required>
                                <option value="admin" <?php if ($edit_user['Rola'] == 'admin') echo 'selected'; ?>>Admin</option>
                                <option value="user" <?php if ($edit_user['Rola'] == 'user') echo 'selected'; ?>>User</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" name="edit_user">Zapisz zmiany</button>
                            <button type="button" onclick="cancelEdit()">Anuluj</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
        function searchUsers() {
    const input = document.getElementById('search-input');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('user-table');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const tdImie = tr[i].getElementsByTagName('td')[1];
        const tdNazwisko = tr[i].getElementsByTagName('td')[2];
        const tdEmail = tr[i].getElementsByTagName('td')[3];
        const tdRola = tr[i].getElementsByTagName('td')[4];

        if (tdImie || tdNazwisko || tdEmail || tdRola) {
            const imieValue = tdImie.textContent || tdImie.innerText;
            const nazwiskoValue = tdNazwisko.textContent || tdNazwisko.innerText;
            const emailValue = tdEmail.textContent || tdEmail.innerText;
            const rolaValue = tdRola.textContent || tdRola.innerText;
            const combinedText = `${imieValue} ${nazwiskoValue} ${emailValue} ${rolaValue}`.toLowerCase();

            tr[i].style.display = combinedText.indexOf(filter) > -1 ? '' : 'none';
        }
    }
    }


        function cancelEdit() {
            // Resetowanie zapytania GET
            history.replaceState(null, document.title, window.location.pathname);

            // Ukrycie formularza edycji
            document.getElementById('edit-form').style.display = 'none';
            document.getElementById('napis').style.display = 'none';
            const editForm = document.getElementById('edit-form');
            if (editForm) {
                // Przewijamy stronę do formularza edycji
                editForm.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
</body>
</html>

