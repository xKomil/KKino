<?php
require_once "config/config.php";

$imie = $nazwisko = $email = $haslo = $powtorz_haslo = "";
$imie_err = $nazwisko_err = $email_err = $haslo_err = $powtorz_haslo_err = "";
$email_exists = false;
$mysql_error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Walidacja imienia
    if (empty(trim($_POST["imie"]))) {
        $imie_err = "Proszę podać imię.";
    } else {
        $imie = trim($_POST["imie"]);
    }

    // Walidacja nazwiska
    if (empty(trim($_POST["nazwisko"]))) {
        $nazwisko_err = "Proszę podać nazwisko.";
    } else {
        $nazwisko = trim($_POST["nazwisko"]);
    }

    // Walidacja adresu email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Proszę podać adres email.";
    } else {
        $email = trim($_POST["email"]);
        // Sprawdzenie, czy email już istnieje w bazie
        $sql = "SELECT id FROM ${prefix}Użytkownicy WHERE Email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $email_err = "Konto z tym adresem email już istnieje.";
                    $email_exists = true;
                }
            } else {
                $mysql_error_msg = "Błąd podczas sprawdzania emaila: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Walidacja hasła
    if (empty(trim($_POST["haslo"]))) {
        $haslo_err = "Proszę podać hasło.";
    } elseif (strlen(trim($_POST["haslo"])) < 6) {
        $haslo_err = "Hasło musi zawierać co najmniej 6 znaków.";
    } else {
        $haslo = trim($_POST["haslo"]);
    }

    // Walidacja powtórzenia hasła
    if (empty(trim($_POST["powtorz_haslo"]))) {
        $powtorz_haslo_err = "Proszę powtórzyć hasło.";
    } else {
        $powtorz_haslo = trim($_POST["powtorz_haslo"]);
        if (empty($haslo_err) && ($haslo != $powtorz_haslo)) {
            $powtorz_haslo_err = "Podane hasła nie są takie same.";
        }
    }

    // Sprawdź, czy nie ma błędów walidacji przed dodaniem do bazy danych
    if (empty($imie_err) && empty($nazwisko_err) && empty($email_err) && empty($haslo_err) && empty($powtorz_haslo_err)) {
        // Zabezpieczenie przed atakami SQL Injection
        $imie = mysqli_real_escape_string($conn, $imie);
        $nazwisko = mysqli_real_escape_string($conn, $nazwisko);
        $email = mysqli_real_escape_string($conn, $email);
        $haslo = mysqli_real_escape_string($conn, $haslo);
        $haslo = password_hash($haslo, PASSWORD_DEFAULT); // Haszowanie hasła

        // Zapytanie SQL
        $sql = "INSERT INTO ${prefix}Użytkownicy (Imię, Nazwisko, Email, Hasło, Rola) VALUES ('$imie', '$nazwisko', '$email', '$haslo', 'user')";

        if (mysqli_query($conn, $sql)) {
            // Jeśli rejestracja powiodła się, przekieruj użytkownika do strony logowania
            header("Location: logowanie.php");
            exit();
        } else {
            $mysql_error_msg = "Błąd podczas rejestracji: Taki e-mail już istnieje :)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarejestruj się w KKino!</title>
    <link rel="stylesheet" href="css/rejestracjastyle.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <style>
        .help-block {
            color: red;
            font-size: 0.9em;
            margin-top: 0.2em;
        }
    </style>
</head>
<body>
    <div class="karta-logowania-container">
        <div class="karta-logowania">
            <div class="karta-logowania-logo">
                <img src="assets/KK Cinema.ico" alt="logo" />
            </div>
            <div class="karta-logowania-header">
                <h1>Zarejestruj się</h1>
                <div>Wpisz swoje dane, w celu rejestracji</div>
            </div>
            <form id="registrationForm" class="karta-logowania-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">account_circle</span>
                    <input type="text" name="imie" placeholder="Wpisz swoje imię" value="<?php echo htmlspecialchars($imie); ?>" required pattern="[a-zA-ZżźćńółęąśŻŹĆĄŚĘŁÓŃ]+" title="Imię może zawierać tylko litery." autofocus>
                    <span class="help-block"><?php echo $imie_err; ?></span>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">account_circle</span>
                    <input type="text" name="nazwisko" placeholder="Wpisz swoje nazwisko" value="<?php echo htmlspecialchars($nazwisko); ?>" required pattern="[a-zA-ZżźćńółęąśŻŹĆĄŚĘŁÓŃ]+" title="Nazwisko może zawierać tylko litery.">
                    <span class="help-block"><?php echo $nazwisko_err; ?></span>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">mail</span>
                    <input type="email" name="email" placeholder="Wpisz e-mail" value="<?php echo htmlspecialchars($email); ?>" required>
                    <span class="help-block"><?php echo $email_err; ?></span>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">lock</span>
                    <input type="password" id="haslo" name="haslo" placeholder="Wpisz hasło" required>
                    <span id="haslo_err" class="help-block"><?php echo $haslo_err; ?></span>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">lock</span>
                    <input type="password" id="powtorz_haslo" name="powtorz_haslo" placeholder="Wpisz hasło ponownie" required>
                    <span id="powtorz_haslo_err" class="help-block"><?php echo $powtorz_haslo_err; ?></span>
                </div>
                <button type="submit" name="submit">Zarejestruj się</button>
                <a class="powrot" href="logowanie.php"><p>POWRÓT DO LOGOWANIA</p></a>
            </form>
            <?php if($email_exists): ?>
                <div class="form-rzecz">
                    <span class="help-block">Konto z tym adresem email już istnieje.</span>
                </div>
            <?php endif; ?>
            <?php if(!empty($mysql_error_msg)): ?>
                <div class="form-rzecz">
                    <span class="help-block"><?php echo $mysql_error_msg; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html
