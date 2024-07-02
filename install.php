<?php
session_start();

function url_origin($s, $use_forwarded_host = false) {
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host = false) {
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

$url = pathinfo(full_url($_SERVER));
$base_url = $url['dirname'] . "/";

$config_file = 'config/config.php';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;

switch ($step) {
    case 1:
        step1();
        break;

    case 2:
        step2();
        break;

    case 3:
        step3();
        break;

    case 4:
        step4();
        break;

    case 5:
        step5();
        break;
    
    case 6:
        step6();
        break;
    
    case 7:
        step7();
        break;

    default:
        check_config();
        break;
}

function check_config() {
    global $config_file;

    if (file_exists($config_file)) {
        if (is_writable($config_file)) {
            if (can_connect_db()) {
                echo "<div class='container'><p>Instalacja jest już zakończona. Możesz przejść do <a href='index.php'>strony głównej</a>.</p></div>";
                echo "<script>window.location.href='index.php';</script>"; // Dodanie przekierowania
            } else {
                form_install_1();
            }
        } else {
            echo "<div class='container'><p>Zmień uprawnienia do pliku <code>$config_file</code><br>np. <code>chmod o+w $config_file</code></p>";
            echo "<p><button class='btn-info' onClick='window.location.href=window.location.href'>Odśwież stronę</button></p></div>";
        }
    } else {
        echo "<div class='container'><p>Stwórz plik <code>$config_file</code><br>np. <code>touch $config_file</code></p>";
        echo "<p><button class='btn-info' onClick='window.location.href=window.location.href'>Odśwież stronę</button></p></div>";
    }
}


function can_connect_db() {
    require 'config/config.php';
    return isset($conn) && $conn;
}

function form_install_1() {
    echo '<div class="container">
            <form method="post" action="install.php?step=2">
                <label for="host">Host:</label>
                <input type="text" id="host" name="host" required>
                <label for="user">User:</label>
                <input type="text" id="user" name="user" required>
                <label for="passwd">Password:</label>
                <input type="password" id="passwd" name="passwd" required>
                <label for="dbname">Database Name:</label>
                <input type="text" id="dbname" name="dbname" required>
                <label for="prefix">Table Prefix:</label>
                <input type="text" id="prefix" name="prefix">
                <button type="submit">Next</button>
            </form>
          </div>';
}

function step2() {
    global $config_file;

    $file = fopen($config_file, "w");
    $config = "<?php\n";
    $config .= "\$host='" . $_POST['host'] . "';\n";
    $config .= "\$user='" . $_POST['user'] . "';\n";
    $config .= "\$password='" . $_POST['passwd'] . "';\n";
    $config .= "\$dbname='" . $_POST['dbname'] . "';\n";
    $config .= "\$prefix='" . $_POST['prefix'] . "';\n";
    $config .= "\$conn = mysqli_connect(\$host, \$user, \$password, \$dbname);\n\n";

    if (!fwrite($file, $config)) {
        echo "<div class='container'>Nie mogę zapisać do pliku ($config_file)</div>";
        exit;
    }

    echo "<div class='container'><p>Krok 2 zakończony: Plik konfiguracyjny utworzony</p>";
    fclose($file);

    echo '<p><a href="install.php?step=3">Przejdź do kroku 3</a></p></div>';
}


function step3() {
    require_once 'config/config.php';

    if ($conn) {
        echo "<div class='container'><p>Połączono z bazą danych</p>";
        echo '<p><a href="install.php?step=4">Przejdź do kroku 4</a></p></div>';
    } else {
        echo "<div class='container'><p>Nie można połączyć się z bazą danych</p>";
        echo '<p><a href="install.php?step=2">Powrót do kroku 2</a></p></div>';
    }
}

function step4() {
    require_once 'config/config.php';

    $prefix = $prefix ? $prefix : '';

    $create_tables = [];
    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Bilety` (
        ID_biletu INT AUTO_INCREMENT PRIMARY KEY,
        Nazwa_biletu VARCHAR(255) NOT NULL,
        Cena DECIMAL(10, 2) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Filmy` (
        ID_filmu INT AUTO_INCREMENT PRIMARY KEY,
        Tytuł VARCHAR(255) NOT NULL,
        Reżyser VARCHAR(255) NOT NULL,
        Gatunek VARCHAR(255) NOT NULL,
        Czas_trwania INT NOT NULL,
        Opis TEXT NOT NULL,
        Zdjecie VARCHAR(255) NOT NULL,
        Zwiastun VARCHAR(255) NOT NULL,
        Rok_powstania YEAR NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Sale` (
        ID_sali INT AUTO_INCREMENT PRIMARY KEY,
        Numer_sali INT NOT NULL,
        Liczba_miejsc INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Użytkownicy` (
        ID_użytkownika INT AUTO_INCREMENT PRIMARY KEY,
        Imię VARCHAR(255) NOT NULL,
        Nazwisko VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL UNIQUE,
        Hasło VARCHAR(255) NOT NULL,
        Rola ENUM('user', 'admin') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Wiadomosci` (
        ID_wiadomosci INT AUTO_INCREMENT PRIMARY KEY,
        Uzytkownik VARCHAR(255) NOT NULL,
        Temat VARCHAR(255) NOT NULL,
        Wiadomosc TEXT NOT NULL,
        Data_wyslania TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Seanse` (
        ID_seansu INT AUTO_INCREMENT PRIMARY KEY,
        ID_filmu INT NOT NULL,
        ID_sali INT NOT NULL,
        Data DATE NOT NULL,
        Godzina TIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    $create_tables[] = "CREATE TABLE IF NOT EXISTS `${prefix}Rezerwacje` (
        ID_rezerwacji INT AUTO_INCREMENT PRIMARY KEY,
        ID_biletu INT NOT NULL,
        ID_użytkownika INT NOT NULL,
        Miejsce VARCHAR(10) NOT NULL,
        ID_filmu INT NOT NULL,
        ID_seansu INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;";

    echo "<div class='container'>";
    foreach ($create_tables as $query) {
        if (mysqli_query($conn, $query)) {
            echo "<p>Wykonano: <code>$query</code></p>";
        } else {
            echo "<p>Błąd: " . mysqli_error($conn) . "</p>";
        }
    }

    $alter_tables = [];
    $alter_tables[] = "ALTER TABLE `${prefix}Rezerwacje`
        ADD FOREIGN KEY (ID_biletu) REFERENCES `${prefix}Bilety`(ID_biletu),
        ADD FOREIGN KEY (ID_użytkownika) REFERENCES `${prefix}Użytkownicy`(ID_użytkownika),
        ADD FOREIGN KEY (ID_filmu) REFERENCES `${prefix}Filmy`(ID_filmu),
        ADD FOREIGN KEY (ID_seansu) REFERENCES `${prefix}Seanse`(ID_seansu);";

    $alter_tables[] = "ALTER TABLE `${prefix}Seanse`
        ADD FOREIGN KEY (ID_filmu) REFERENCES `${prefix}Filmy`(ID_filmu),
        ADD FOREIGN KEY (ID_sali) REFERENCES `${prefix}Sale`(ID_sali);";

    foreach ($alter_tables as $query) {
        if (mysqli_query($conn, $query)) {
            echo "<p>Wykonano: <code>$query</code></p>";
        } else {
            echo "<p>Błąd: " . mysqli_error($conn) . "</p>";
        }
    }

    echo '<p><a href="install.php?step=5">Przejdź do kroku 5</a></p></div>';
}

function step5() {
    require_once 'config/config.php';

    $prefix = $prefix ? $prefix : '';

    $insert_data = [];
    $insert_data[] = "INSERT INTO `${prefix}Bilety` (ID_biletu, Nazwa_biletu, Cena)
    VALUES
    (1, 'Normalny', 20.00),
    (2, 'Ulgowy', 14.00),
    (3, 'Specjalny', 150.00);";

    $insert_data[] = "INSERT INTO `${prefix}Filmy` (ID_filmu, Tytuł, Reżyser, Gatunek, Czas_trwania, Opis, Zdjecie, Zwiastun, Rok_powstania)
    VALUES
    (1, 'Interstellar', 'Christopher Nolan', 'Dokumentalne', 169, 'Podróż kosmiczna w poszukiwaniu nowego domu dla ludzkości.', 'https://m.media-amazon.com/images/I/91JnoM0khKL._AC_UF1000,1000_QL80_.jpg', 'https://www.youtube.com/watch?v=zSWdZVtXT7E', 2014),
    (2, 'Pulp Fiction', 'Quentin Tarantino', 'Komedie', 154, 'Zestawienie losowo ułożonych historii związanych z przestępczością i przemocą.', 'https://m.media-amazon.com/images/S/pv-target-images/dbb9aff6fc5fcd726e2c19c07f165d40aa7716d1dee8974aae8a0dad9128d392.jpg', 'https://www.youtube.com/watch?v=tGpTpVyI_OQ', 1994),
    (3, 'Avengers: Endgame', 'Anthony Russo, Joe Russo', 'Akcji', 182, 'Po porażce ze strony Thanosa, Avengers zbierają się ponownie, aby przywrócić porządek we wszechświecie.', 'https://m.media-amazon.com/images/M/MV5BMTc5MDE2ODcwNV5BMl5BanBnXkFtZTgwMzI2NzQ2NzM@._V1_.jpg', 'https://www.youtube.com/watch?v=TcMBFSGVi1c', 2019),
    (4, 'Inception', 'Christopher Nolan', 'Science Fiction', 148, 'Zespół specjalistów wkracza w świadomość ludzi, aby manipulować ich snami.', 'https://m.media-amazon.com/images/M/MV5BMjAxMzY3NjcxNF5BMl5BanBnXkFtZTcwNTI5OTM0Mw@@._V1_.jpg', 'https://www.youtube.com/watch?v=5EiV_HXIIGs', 2010),
    (5, 'The Dark Knight', 'Christopher Nolan', 'Akcji', 152, 'Batman staje do walki ze złoczyńcą znanym jako Joker, który szerzy chaos w Gotham City.', 'https://m.media-amazon.com/images/M/MV5BMTMxNTMwODM0NF5BMl5BanBnXkFtZTcwODAyMTk2Mw@@._V1_.jpg', 'https://www.youtube.com/watch?v=_PZpmTj1Q8Q', 2008),
    (6, 'Fight Club', 'David Fincher', 'Dramat', 139, 'Historia człowieka cierpiącego na bezsenność, który zakłada klub walki jako sposób na ucieczkę od rutyny życia.', 'https://m.media-amazon.com/images/M/MV5BMmEzNTkxYjQtZTc0MC00YTVjLTg5ZTEtZWMwOWVlYzY0NWIwXkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_FMjpg_UX1000_.jpg', 'https://www.youtube.com/watch?v=O1nDozs-LxI', 1999),
    (7, 'Forrest Gump', 'Robert Zemeckis', 'Dramat', 142, 'Historia życia Forresta Gumpa, który uczestniczy w wielu znaczących wydarzeniach historycznych w Stanach Zjednoczonych.', 'https://m.media-amazon.com/images/S/pv-target-images/2d0c9e38968936e6711c7fb2bc7895b82d8bb9178b5a854e14dffa4b17b88487.jpg', 'https://www.youtube.com/watch?v=bLvqoHBptjg', 1994),
    (8, 'Matrix', 'Lana Wachowski, Lilly Wachowski', 'Akcji', 136, 'Programista komputerowy Neo dowiaduje się od tajemniczych rebeliantów, że rzeczywistość, którą zna, jest iluzją stworzona przez maszynę.', 'https://m.media-amazon.com/images/M/MV5BNzQzOTk3OTAtNDQ0Zi00ZTVkLWI0MTEtMDllZjNkYzNjNTc4L2ltYWdlXkEyXkFqcGdeQXVyNjU0OTQ0OTY@._V1_.jpg', 'https://www.youtube.com/watch?v=vKQi3bBA1y8', 1999),
    (9, 'Zakonnica', 'Corin Hardy', 'Horrory', 96, 'Film opowiada historię młodej zakonnicy i zakonnika na wiejskim podwórku w Rumunii, który ma stać się areną przerażającej walki między żywymi a umarłymi. Ich zadaniem jest zbadać tajemniczy samobójczy przypadek zakonnicy.', 'https://media.multikino.pl/thumbnails/50/rc/NkIwMjY5/eyJ0aHVtYm5haWwiOnsic2l6ZSI6WyIxMDAwMCIsIjEwMDAwIl0sIm1vZGUiOiJpbnNldCJ9fQ==/uploads/images/films_and_events/zakonnica-pl2_9d73a2ad9f.jpg', 'https://www.youtube.com/watch?v=IfNIK883Fho', 2018),
    (10, 'Chłopi', 'DK Welchman, Hugh Welchman', 'Dramat', 116, 'Na tle zmieniających się pór roku i sezonowych prac polowych rozgrywają się losy rodziny Borynów i pięknej, tajemniczej Jagny.', 'https://fwcdn.pl/fpo/79/62/857962/8095499_1.3.jpg', 'https://www.youtube.com/watch?v=rILKSimhfA4', 2023);";

    $insert_data[] = "INSERT INTO `${prefix}Sale` (ID_sali, Numer_sali, Liczba_miejsc)
    VALUES
    (1, 1, 100),
    (2, 2, 80),
    (3, 3, 120),
    (4, 4, 90),
    (5, 5, 110),
    (6, 6, 70),
    (7, 7, 100),
    (8, 8, 85);";

    $insert_data[] = "INSERT INTO `${prefix}Wiadomosci` (ID_wiadomosci, Uzytkownik, Temat, Wiadomosc, Data_wyslania)
    VALUES
    (1, 1, 'Błąd podczas procesu płatności', 'Witam, mam problem z dokonaniem płatności na Waszej stronie. Podczas próby zapłaty za zakupy, po wprowadzeniu danych karty kredytowej, pojawia się komunikat o błędzie i proces płatności nie zostaje ukończony. Próbowałam używać różnych kart, ale problem się powtarza. Chciałabym wiedzieć, czy jest to problem po stronie mojej karty, czy może Waszego systemu płatności. Czy możecie mi pomóc rozwiązać ten problem? Chcę jak najszybciej sfinalizować swoje zakupy. Z góry dziękuję za pomoc. Pozdrawiam, Anna Kowalska.', '2024-06-14 15:15:08'),
    (2, 2, 'Problem z logowaniem', 'Cześć, mam problem z zalogowaniem się do mojego konta na Waszej stronie. Używam poprawnego loginu i hasła, ale za każdym razem, gdy próbuję się zalogować, pojawia się komunikat o błędnych danych. Próbowałem już resetować hasło, ale nawet po jego zmianie sytuacja się nie poprawiła. Czy moglibyście sprawdzić, co może być przyczyną tego problemu? Moje konto jest dla mnie bardzo ważne, ponieważ mam na nim dostęp do wielu istotnych informacji i narzędzi. Z góry dziękuję za pomoc. Pozdrawiam, Jan Nowak.', '2024-06-14 15:15:24'),
    (3, 3, 'Brak potwierdzenia', 'Dzień dobry, kilka dni temu złożyłem zamówienie na Waszej stronie, ale do tej pory nie otrzymałem żadnego potwierdzenia na maila. Numer zamówienia to 12345. Chciałbym wiedzieć, czy moje zamówienie zostało poprawnie zarejestrowane i kiedy mogę spodziewać się jego realizacji. Sprawdzałem foldery spam i inne, ale żadnego maila od Was nie znalazłem. Proszę o pilne wyjaśnienie tej sytuacji, ponieważ zależy mi na szybkim otrzymaniu zamówionych produktów. Dziękuję za pomoc. Pozdrawiam, Piotr Zieliński.', '2024-06-14 15:15:37'),
    (4, 4, 'Problem', 'Witam, od kilku dni mam problemy z działaniem Waszej aplikacji mobilnej. Aplikacja często się zawiesza i wyłącza, co uniemożliwia mi normalne korzystanie z jej funkcji. Próbowałam ją reinstalować oraz aktualizować system na moim telefonie, ale problemy nadal występują. Czy możecie sprawdzić, czy są jakieś znane problemy z tą wersją aplikacji? Bardzo proszę o pomoc w rozwiązaniu tego problemu, ponieważ często korzystam z Waszej aplikacji w codziennej pracy. Dziękuję za pomoc. Pozdrawiam, Maria Wiśniewska.', '2024-06-14 15:15:47');";

    $insert_data[] = "INSERT INTO `${prefix}Seanse` (ID_seansu, ID_filmu, ID_sali, Data, Godzina)
    VALUES
    (1, 1, 1, '2024-06-03', '18:00:00'),
    (2, 2, 2, '2024-06-03', '20:00:00'),
    (3, 3, 3, '2024-06-04', '17:30:00'),
    (4, 4, 4, '2024-06-04', '19:00:00'),
    (5, 5, 5, '2024-06-05', '18:30:00'),
    (6, 6, 6, '2024-06-05', '20:00:00'),
    (7, 7, 7, '2024-06-06', '17:45:00'),
    (8, 8, 8, '2024-06-06', '19:30:00');";

    echo '<div>';
    foreach ($insert_data as $query) {
        if (mysqli_query($conn, $query)) {
            echo "<p>Wykonano: <code>$query</code></p>";
        } else {
            echo "<p>Błąd: " . mysqli_error($conn) . "</p>";
        }
    }
    echo '<p><a href="install.php?step=6">Przejdź do kroku 6</a></p></div>';
}

function step6($error_message = '') {
    echo '<div class="container">';
    if ($error_message) {
        echo "<p style='color:red;'>$error_message</p>";
    }
    echo '<form method="post" action="install.php?step=7">
        <label for="base_url">Nazwa serwisu (base url):</label>
        <input type="text" id="base_url" name="base_url" required>

        <label for="nazwa_aplikacji">Nazwa aplikacji:</label>
        <input type="text" id="nazwa_aplikacji" name="nazwa_aplikacji" required>

        <label for="data_powstania">Data powstania:</label>
        <input type="date" id="data_powstania" name="data_powstania" required>

        <label for="wersja">Wersja:</label>
        <input type="text" id="wersja" name="wersja" required>

        <label for="brand">Nazwa firmy:</label>
        <input type="text" id="brand" name="brand" required>

        <label for="adres1">Ulica:</label>
        <input type="text" id="adres1" name="adres1" required>

        <label for="adres2">Miasto, kod:</label>
        <input type="text" id="adres2" name="adres2" required>

        <label for="adres3">Telefon:</label>
        <input type="text" id="adres3" name="adres3" required>

        <label for="admin_imie">Imię administratora:</label>
        <input type="text" id="admin_imie" name="admin_imie" required>

        <label for="admin_nazwisko">Nazwisko administratora:</label>
        <input type="text" id="admin_nazwisko" name="admin_nazwisko" required>

        <label for="admin_email">Email administratora:</label>
        <input type="email" id="admin_email" name="admin_email" required>

        <label for="passwd">Hasło administratora:</label>
        <input type="password" id="passwd" name="passwd" required>

        <label for="passwd_confirm">Potwierdź hasło:</label>
        <input type="password" id="passwd_confirm" name="passwd_confirm" required>
        <button type="submit">Krok 7</button>
    </form></div>';
}


function step7() {
    global $config_file;
    require_once 'config/config.php';

    $prefix = $prefix ? $prefix : '';
    // Sprawdzenie, czy hasła są identyczne
    if ($_POST['passwd'] !== $_POST['passwd_confirm']) {
        step6('Hasła nie są identyczne. Proszę spróbować ponownie.');
        return;
    }

    $config .= "\n# Konfiguracja aplikacji\n";
    $config .= "\$base_url='" . $_POST['base_url'] . "';\n";
    $config .= "\$nazwa_aplikacji='" . $_POST['nazwa_aplikacji'] . "';\n";
    $config .= "\$data_powstania='" . $_POST['data_powstania'] . "';\n";
    $config .= "\$wersja='" . $_POST['wersja'] . "';\n";
    $config .= "\$brand='" . $_POST['brand'] . "';\n";
    $config .= "\$adres1='" . $_POST['adres1'] . "';\n";
    $config .= "\$adres2='" . $_POST['adres2'] . "';\n";
    $config .= "\$adres3='" . $_POST['adres3'] . "';\n";
    $config .= "\$admin_imie='" . $_POST['admin_imie'] . "';\n";
    $config .= "\$admin_nazwisko='" . $_POST['admin_nazwisko'] . "';\n";
    $config .= "\$admin_email='" . $_POST['admin_email'] . "';\n";
    $config .= "\$admin_rola='admin';\n";
    $config .= "\$passwd='" . $_POST['passwd'] . "';\n";
    $config .= "\$passwd='" . password_hash($_POST['passwd'], PASSWORD_DEFAULT) . "';\n";
    $config .= "?>\n";

    if (is_writable($config_file)) {
        if (!$uchwyt = fopen($config_file, 'a')) {
            echo "<div class='container'>Nie można otworzyć pliku ($config_file)</div>";
            exit;
        }
        if (fwrite($uchwyt, $config) === FALSE) {
            echo "<div class='container'>Nie można zapisać do pliku ($config_file)</div>";
            exit;
        }
        echo "<div class='container'>Sukces, zapisano konfigurację do pliku ($config_file).</div>";
        fclose($uchwyt);
    } else {
        echo "<div class='container'>Plik $config_file nie jest zapisywalny</div>";
        return;
    }


    // Dane administratora
    $admin_imie = $_POST['admin_imie'];
    $admin_nazwisko = $_POST['admin_nazwisko'];
    $admin_email = $_POST['admin_email'];
    $passwd_hashed = password_hash($_POST['passwd'], PASSWORD_DEFAULT);
    $admin_rola = 'admin';

    // Dodanie administratora do bazy danych
    $stmt = $conn->prepare("INSERT INTO `${prefix}Użytkownicy` (Imię, Nazwisko, Email, Hasło, Rola) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sssss', $admin_imie, $admin_nazwisko, $admin_email, $passwd_hashed, $admin_rola);

        if ($stmt->execute()) {
            echo "<div class='container'><p>Dodano administratora</p></div>";
        } else {
            echo "<div class='container'><p>Błąd przy dodawaniu administratora: " . $stmt->error . "</p>";
            echo '<p><a href="install.php?step=7">Powrót do kroku 7</a></p></div>';
        }

        $stmt->close();
    } else {
        echo "<div class='container'><p>Błąd przy przygotowywaniu zapytania: " . $conn->error . "</p>";
        echo '<p><a href="install.php?step=7">Powrót do kroku 7</a></p></div>';
    }

    $conn->close();

    echo '<p><a href="index.php">Przejdź do strony głównej</a></p>';
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalacja KKino</title>
    <style>
        /* Resetowanie domyślnych styli */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Podstawowe style dla całego formularza */
.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-top: 20px;
}

.container h2 {
    margin-bottom: 20px;
    font-size: 1.5rem;
    text-align: center;
}

form {
    display: grid;
    gap: 10px;
}

label {
    font-weight: bold;
}

input[type="text"],
input[type="password"],
input[type="email"],
input[type="date"] {
    width: calc(100% - 20px); /* 100% szerokości kontenera z odjęciem marginesu */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 1rem;
}

input[type="text"]:focus,
input[type="password"]:focus,
input[type="email"]:focus,
input[type="date"]:focus {
    outline: none;
    border-color: #66afe9;
}

button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 1rem;
}

button:hover {
    background-color: #45a049;
}

button:active {
    background-color: #3e8e41;
}


        @media (max-width: 600px) {
            .container {
                width: 100%;
                padding: 10px;
            }

            button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
</body>
</html>
