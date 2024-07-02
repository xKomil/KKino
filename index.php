<?php
// Ścieżka do pliku konfiguracyjnego
$config_path = 'config/config.php';

// Sprawdzanie istnienia pliku konfiguracyjnego
if (file_exists($config_path)) {
    // Wczytanie pliku konfiguracyjnego
    require_once $config_path;

    // Sprawdzenie istnienia zmiennych potrzebnych do połączenia z bazą danych
    if (isset($host, $user, $password, $dbname, $prefix)) {
        // Połączenie z bazą danych
        $conn = new mysqli($host, $user, $password, $dbname);

        // Sprawdzenie poprawności połączenia
        if ($conn->connect_error) {
            // Błąd połączenia z bazą danych, przekierowanie do instalacji
            header("Location: install.php");
            exit();
        } else {
            // Połączenie udane, zamknięcie połączenia
            $conn->close();
        }
    } else {
        // Brak wymaganych zmiennych w pliku konfiguracyjnym, przekierowanie do instalacji
        header("Location: install.php");
        exit();
    }
} else {
    // Plik konfiguracyjny nie istnieje, przekierowanie do instalacji
    header("Location: install.php");
    exit();
}
?>


<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: logowanie.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] == true) {
    session_destroy();
    header("Location: logowanie.php");
    exit();
}

$config_path = 'config/config.php';
include $config_path;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $uzytkownik = $_POST['uzytkownik'];
    $temat = $_POST['temat'];
    $wiadomosc = $_POST['wiadomosc'];
    $currentDateTime = date('Y-m-d H:i:s');

    // Połączenie z bazą danych
    $conn = new mysqli($host, $user, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Połączenie nieudane: " . $conn->connect_error);
    }

    $sql = "INSERT INTO ${prefix}Wiadomosci (Uzytkownik, Temat, Wiadomosc, Data_wyslania) VALUES ('$uzytkownik', '$temat', '$wiadomosc', '$currentDateTime')";
    if ($conn->query($sql) === TRUE) {
        $message = "Wiadomość została wysłana pomyślnie.";
        // Przekierowanie po wysłaniu wiadomości
        header("Location: " . $_SERVER['PHP_SELF'] . "#contact");
        exit();
    } else {
        $message = "Błąd: " . $conn->error;
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKino</title>

    <!-- 
    - favicon ikonka do strony naszej :D (robilem ten syf w adobe illustrator godzine wiec nie ruszaj blagam kamil p)
  -->
    <link rel="shortcut icon" href="assets/KK Cinema.ico" type="image/svg+xml">
    

    <!-- 
    - custom css linki tutaj zeby syfu nie było
  -->
    
  <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>" />

  
    <link rel="stylesheet" href="css/bilety.css">

    <!-- 
    - google font link bardzo potrzebne bo musimy mieć ładne czcionki i takie tam :D
  -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<body id="top">

    <!-- 
    - #HEADER
  -->

    <header class="header" data-header>
        <div class="container">

            <div class="overlay" data-overlay></div>

            <a href="#" class="logo">
                <img src="assets/KK Cinema.ico" alt="logo KKino"><span>
            </a>

            <div class="header-actions">


                <div class="lang-wrapper">
                    <label for="language">
                        <ion-icon name="globe-outline"></ion-icon>
                    </label>

                    <select name="language" id="language">
                        <option value="en">PL</option>
                        <option value="en">ENG(kiedyś :))</option> 
                        <!-- nigdy tego nie dociągniemy jestem pewien-->
                    </select>
                </div>

                <form action="" method="post">
                <input type="hidden" name="logout" value="true">
                <button type="submit" class="btn btn-primary">Wyloguj się</button>
                </form>


            </div>

            <button class="menu-open-btn" data-menu-open-btn>
                <ion-icon name="reorder-two"></ion-icon>
            </button>

            <nav class="navbar" data-navbar>

                <div class="navbar-top">


                    <button class="menu-close-btn" data-menu-close-btn>
                        <ion-icon name="close-outline"></ion-icon>
                    </button>

                </div>

                <ul class="navbar-list">

                    <li>
                        <a href="#home" class="navbar-link">Strona domowa</a>
                    </li>

                    <li>
                        <a href="#movie" class="navbar-link">Filmy</a>
                    </li>


                    <li>
                        <a href="#tv-series" class="navbar-link">Bilety</a>
                    </li>

                    <li>
                        <a href="#contact" class="navbar-link">Kontakt</a>
                    </li>
                    <li>
                        <a href="userprofile.php" class="navbar-link">Profil</a>
                    </li>

                </ul>

                

            </nav>

        </div>
    </header>





    <main>
        <article>

            <!-- 
        - #HERO
      -->
      
<section class="hero" id="home"  style="background-image: url('assets/chlopi.png'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="hero-content">
            <h1 class="h1 hero-title">
                <strong>ULUBIONY FILM</strong> TWÓRCÓW
            </h1>
            <div class="meta-wrapper">
                <div class="badge-wrapper">
                    <div class="badge badge-outline">4K</div>
                </div>
                <div class="date-time">
                    <div>
                        <ion-icon name="calendar-outline"></ion-icon>
                        <time id="film-year" datetime="rokprodukcjifilmu">2023</time>
                    </div>
                    <div>
                        <ion-icon name="time-outline"></ion-icon>
                        <time id="film-duration" datetime="PT116M">116 min</time>
                    </div>
                </div>
            </div>

            <a class="btn btn-primary" href="https://www.youtube.com/watch?v=rILKSimhfA4" target="_blank">
                <ion-icon name="play"></ion-icon>
                Zobacz zwiastun
            </a>
        </div>
    </div>
</section>




<!-- index.php -->

<!-- #TOP RATED -->
<section class="top-rated" id="movie">
    <div class="container">
        <?php
        // Pobieranie filmów z bazy danych
        $sql = "SELECT Tytuł, Reżyser, Gatunek, Czas_trwania, Opis, Zdjecie, Zwiastun, Rok_powstania FROM ${prefix}Filmy";
        $result = mysqli_query($conn, $sql);

        $filmy = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $filmy[] = $row;
            }
        }
        ?>

        <p class="section-subtitle">tylko u nas zobaczysz</p>
        <h2 class="h2 section-title">Najlepsze seanse</h2>        

        <ul class="filter-list">
            <li><button class="filter-btn" onclick="filterMovies('Wszystko')">Wszystko</button></li>
            <li><button class="filter-btn" onclick="filterMovies('Akcji')">Akcji</button></li>
            <li><button class="filter-btn" onclick="filterMovies('Horrory')">Horrory</button></li>
            <li><button class="filter-btn" onclick="filterMovies('Dokumentalne')">Dokumentalne</button></li>
            <li><button class="filter-btn" onclick="filterMovies('Komedie')">Komedie</button></li>
            <li><button class="filter-btn" onclick="filterMovies('Dramat')">Dramat</button></li> 
            <li><button class="filter-btn" onclick="filterMovies('Science Fiction')">Science Fiction</button></li> 
        </ul>

        <ul class="movies-list" id="movies-list">
            <?php foreach ($filmy as $film): ?>
                <li class="movie-item" data-genre="<?php echo htmlspecialchars($film['Gatunek']); ?>">
                    <div class="movie-card">
                        <a href="<?php echo htmlspecialchars($film['Zwiastun']); ?>" target="_blank">
                            <figure class="card-banner">
                                <img src="<?php echo htmlspecialchars($film['Zdjecie']); ?>" alt="<?php echo htmlspecialchars($film['Tytuł']); ?>">
                            </figure>
                        </a>
                        <div class="title-wrapper">
                            <!-- Dodanie linku do tytułu -->
                            <a href="<?php echo htmlspecialchars($film['Zwiastun']); ?>" target="_blank">
                                <h3 class="card-title"><?php echo htmlspecialchars($film['Tytuł']); ?></h3>
                            </a>
                            <span><?php echo htmlspecialchars($film['Rok_powstania']); ?></span>
                        </div>
                        <div class="card-meta">
                            <div class="badge badge-outline">4K</div>
                            <div class="duration">
                                <ion-icon name="time-outline"></ion-icon>
                                <time datetime="PT<?php echo htmlspecialchars($film['Czas_trwania']); ?>M"><?php echo htmlspecialchars($film['Czas_trwania']); ?> min</time>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<script>
    // Funkcja do filtrowania filmów
    function filterMovies(genre) {
        // Pobranie wszystkich elementów filmów
        var movies = document.getElementsByClassName('movie-item');

        // Iteracja przez wszystkie elementy filmów
        for (var i = 0; i < movies.length; i++) {
            // Sprawdzenie, czy gatunek filmu odpowiada wybranemu gatunkowi
            if (genre === 'Wszystko' || movies[i].getAttribute('data-genre') === genre) {
                // Wyświetlenie filmu, jeśli pasuje do wybranego gatunku
                movies[i].style.display = 'block';
            } else {
                // Ukrycie filmu, jeśli nie pasuje do wybranego gatunku
                movies[i].style.display = 'none';
            }
        }
    }
</script>





       <!-- 
- #BILETY    (NIE TV-SERIES)
-->

<!-- 
- #BILETY (NIE TV-SERIES) nie zmieniac tych id ani class bo sie wszystko posypie w tym css (dziala? zostaw ~ Sz.P. Marcin Krupski)
-->

<section class="tv-series" id="tv-series">
    <div class="container">
        
        <?php
        $sql = "SELECT Nazwa_biletu, Cena FROM ${prefix}Bilety";
        $result = mysqli_query($conn, $sql);
        
        $bilety = [];
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $bilety[] = $row;
            }
        }
        
        mysqli_close($conn);
        ?>
        <h2 class="h2 section-title">Cennik biletów</h2>
        <ul class="movies-list">
            <?php foreach ($bilety as $bilet): ?>
                <li>
                    <div class="ticket">
                        <div class="ticket-content">
                            <p><?php echo htmlspecialchars($bilet['Nazwa_biletu']); ?></p>
                            <p><?php echo number_format($bilet['Cena'], 2, ',', ' ') . ' PLN'; ?></p>
                        </div>
                        <div class="notch"></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class = "btnkupbilety">
            
        <p class="section-subtitle">ZAREZERWUJ BILET</p>
        <a href="kup_bilet.php" class="btn btn-primary" id="tickets">Zarezerwuj bilet i opłać go na miejscu</a>
        </div>
    </div>
</section>







            <!-- 
        - #CTA
      -->

      <section class="cta" id="contact">
    <div class="container">
        <div class="title-wrapper">
            <h2 class="cta-title">Odezwij się do nas</h2>
        </div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>#contact" method="post" class="cta-form" id="supportForm">
            <input type="text" name="uzytkownik" required placeholder="Twoje imię lub login" class="email-field">
            <input type="text" name="temat" required placeholder="Temat wiadomości" class="email-field">
            <textarea name="wiadomosc" required placeholder="Treść wiadomości" class="email-field" rows="4" maxlength="1000" id="supportMessage"></textarea>
            <div id="charCount">0/1000 znaków</div>
            <button type="submit" name="send_message" class="cta-form-btn">Wyślij</button>
        </form>
    </div>
</section>

<script>
  const textarea = document.getElementById('supportMessage');
  const charCount = document.getElementById('charCount');

  textarea.addEventListener('input', () => {
    const length = textarea.value.length;
    charCount.textContent = `${length}/1000 znaków`;
  });

  document.getElementById('supportForm').addEventListener('submit', (e) => {
    if (textarea.value.length < 10) {
      e.preventDefault();
      alert('Wiadomość musi mieć co najmniej 10 znaków.');
    }
  });
</script>



        </article>
    </main>





    <!-- 
    - #FOOTER
  -->

    
  <?php include 'tocopy/footer.php'; ?>



    <!-- 
    - #GO TO TOP
  -->

    <a href="#top" class="go-top" data-go-top>
        <ion-icon name="chevron-up"></ion-icon>
    </a>





    <!-- 
    - custom js link
  -->
     <script src="script/script.js"></script>

    <!-- 
    - ionicon link
  -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>

</html>