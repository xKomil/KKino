<?php
require_once 'config/config.php';
session_start();

// Sprawdzanie czy formularz został złożony
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pobieranie danych z formularza
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Zabezpieczanie przed atakami SQL Injection
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);
    
    // Zapytanie do bazy danych
    $sql = "SELECT * FROM ${prefix}Użytkownicy WHERE Email = '$email'";
    
    // Wykonanie zapytania
    $result = mysqli_query($conn, $sql);
    
    // Sprawdzenie czy wystąpił błąd związany z nawiązaniem połączenia z bazą danych lub wykonaniem zapytania SQL
    if (!$result) {
        // W przypadku błędu ustawiamy zmienną sesyjną
        $_SESSION['error'] = "Wystąpił błąd podczas logowania. Spróbuj ponownie później.";
    } else {
        // Sprawdzenie czy znaleziono użytkownika o podanym adresie email
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['Hasło'])) {
                // Użytkownik został znaleziony i hasło jest poprawne, ustawiamy zmienną sesji
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $row['ID_użytkownika'];
                $_SESSION['role'] = $row['Rola'];
                
                // Sprawdzamy rolę użytkownika i przekierowujemy go na odpowiednią stronę
                if ($row['Rola'] == 'admin') {
                    header("Location: admin.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                // Hasło jest niepoprawne
                $_SESSION['error'] = "Niepoprawne hasło.";
            }
        } else {
            // Nie znaleziono użytkownika o podanym adresie email
            $_SESSION['error'] = "Nie znaleziono użytkownika o podanym adresie email.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>STRONA LOGOWANIA - Zaloguj się</title>
    <link rel="stylesheet" href="css/loginstyle.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  </head>
  <body>
    <div class="karta-logowania-container">
      <div class="karta-logowania">
        <div class="karta-logowania-logo">
          <img src="assets/KK Cinema.ico" alt="logo">
        </div>
        <div class="karta-logowania-header">
          <h1>Zaloguj się</h1>
          <div>Wpisz swoje dane, w celu zalogowania</div>
        </div>
        <form class="karta-logowania-form" method="POST" action="">
          <div class="form-rzecz">
            <span class="form-rzecz-ikona material-symbols-outlined">mail</span>
            <input type="text" name="email" placeholder="Wpisz e-mail" required autofocus>
          </div>
          <div class="form-rzecz">
            <span class="form-rzecz-ikona material-symbols-outlined">lock</span>
            <input type="password" name="password" placeholder="Wpisz hasło" required>
          </div>
          <div class="form-rzecz-inne">
            <div class="pole-zaznacz">
              <input type="checkbox" id="zapamietajMnieCheckbox">
              <label for="zapamietajMnieCheckbox">Zapamiętaj mnie</label>
            </div>
            <a href="#">Zapomniałem hasła!</a>
          </div>
          <button type="submit">Zaloguj się</button>
        </form>
        <?php
        // Wyświetlanie komunikatu o błędzie, jeśli jest ustawiony
        if (isset($_SESSION['error'])) {
            echo "<div id='errorWindow' class='error-message'>" . $_SESSION['error'] . "</div>";
            // Usuwanie zmiennej sesyjnej po wyświetleniu błędu
            unset($_SESSION['error']);
        }
        ?>
        <div class="karta-logowania-footer">
          Nie masz jeszcze konta?
          <a href="rejestracja.php">Zarejestruj się za darmo</a><br><br>
          <a href="indexGUEST.php">Kontynuuj bez logowania</a>
        </div>
      </div>
      <div class="karta-logowania-social">
        <div>Inna platforma do logowania</div>
        <div class="karta-logowania-social-button">
          <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-facebook" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M7 10v4h3v7h4v-7h3l1-4h-4v-2a1 1 0 0 1 1-1h3v-4h-3a5 5 0 0 0-5 5v2h-3"/>
            </svg>
          </a>
          <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-google" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M20.945 11a9 9 0 1 1-3.284-5.997l-2.655 2.392a5.5 5.5 0 1 0 2.119 6.605h-4.125v-3h7.945z"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </body>
</html>

