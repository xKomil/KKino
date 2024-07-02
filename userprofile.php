<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Połączenie z bazą danych
include_once "config/config.php";

// Pobieranie danych użytkownika
$sql_user = "SELECT Imię, Nazwisko, Email, Hasło FROM ${prefix}Użytkownicy WHERE ID_użytkownika = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

// Obsługa formularza aktualizacji danych
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];
    $new_email = $_POST['email'];

    $sql_update = "UPDATE ${prefix}Użytkownicy SET Imię = ?, Nazwisko = ?, Email = ? WHERE ID_użytkownika = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "sssi", $new_first_name, $new_last_name, $new_email, $user_id);
    mysqli_stmt_execute($stmt_update);

    header("Location: userprofile.php");
    exit();
}

// Obsługa formularza zmiany hasła
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $repeat_password = $_POST['repeat_password'];

    if (password_verify($old_password, $user['Hasło'])) {
        if ($new_password === $repeat_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update_password = "UPDATE ${prefix}Użytkownicy SET Hasło = ? WHERE ID_użytkownika = ?";
            $stmt_update_password = mysqli_prepare($conn, $sql_update_password);
            mysqli_stmt_bind_param($stmt_update_password, "si", $hashed_password, $user_id);
            mysqli_stmt_execute($stmt_update_password);

            header("Location: userprofile.php");
            exit();
        } else {
            echo "Nowe hasła nie są zgodne.";
        }
    } else {
        echo "Stare hasło jest niepoprawne.";
    }
}

// Obsługa formularza usuwania konta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $sql_delete = "DELETE FROM ${prefix}Użytkownicy WHERE ID_użytkownika = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $user_id);
    mysqli_stmt_execute($stmt_delete);

    session_destroy();
    header("Location: logowanie.php");
    exit();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Użytkownika</title>
    <link rel="stylesheet" href="css/profiluser.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="header__wrapper">
      <header></header>
      <div class="cols__container">
        <div class="left__col">
          <div class="img__container">
            <img src="assets/profile-user-icon-isolated-on-white-background-eps10-free-vector" alt="Zdjęcie użytkownika" />
            <span></span>
          </div>
          <h2><?php echo htmlspecialchars($user['Imię']) . ' ' . htmlspecialchars($user['Nazwisko']); ?></h2>
          <p>Użytkownik KKino</p>
          <p><?php echo htmlspecialchars($user['Email']); ?></p>
          <ul class="about"></ul>
          <br> <br>
          <div class="content">
            <p>
              Oto twój profil. Możesz tutaj zarządzać swoimi danymi oraz przeglądać zakupione bilety.
            </p>
          </div>
        </div>
        <div class="right__col">
          <nav>
            <ul>
                <li><a href="userprofile.php">Twoje dane</a></li>
                <li><a href="userprofileBILETY.php">Bilety</a></li>
                <li><a href="user_delete_ticket.php">Usuń bilet</a></li>
                <li><a href="index.php">Wróć do strony głównej</a></li>
            </ul>
          </nav>

          <div class="form-container">
            <h3>Edytuj swoje dane</h3>
            <form action="userprofile.php" method="post">
                <label for="first_name">Imię</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['Imię']); ?>" required>

                <label for="last_name">Nazwisko</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['Nazwisko']); ?>" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>

                <button type="submit" name="update">Zapisz zmiany</button>
            </form>

            <h3>Zmień hasło</h3>
            <form action="userprofile.php" method="post">
                <label for="old_password">Stare hasło</label>
                <input type="password" id="old_password" name="old_password" required>

                <label for="new_password">Nowe hasło</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="repeat_password">Powtórz nowe hasło</label>
                <input type="password" id="repeat_password" name="repeat_password" required>

                <button type="submit" name="change_password">Zmień hasło</button>
            </form>
          </div>
        </div>
      </div>
    </div>
</body>
</html>
