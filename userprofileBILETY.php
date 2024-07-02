<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Połączenie z bazą danych
include "config/config.php";

// Pobieranie danych użytkownika
$sql_user = "SELECT Imię, Nazwisko FROM ${prefix}Użytkownicy WHERE ID_użytkownika = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

// Pobieranie biletów użytkownika
$sql_tickets = "SELECT 
                    r.ID_biletu, r.Miejsce, s.Data, s.Godzina, f.Tytuł AS Film, b.Nazwa_biletu AS Typ_biletu
                FROM ${prefix}Rezerwacje r
                JOIN ${prefix}Filmy f ON r.ID_filmu = f.ID_filmu
                JOIN ${prefix}Seanse s ON r.ID_seansu = s.ID_seansu
                JOIN ${prefix}Bilety b ON r.ID_biletu = b.ID_biletu
                WHERE r.ID_użytkownika = ?";


$stmt_tickets = mysqli_prepare($conn, $sql_tickets);
mysqli_stmt_bind_param($stmt_tickets, "i", $user_id);
mysqli_stmt_execute($stmt_tickets);
$result_tickets = mysqli_stmt_get_result($stmt_tickets);

// Obsługa usuwania biletu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $ticket_id = $_POST['ticket_id'];
    $sql_delete_ticket = "DELETE FROM ${prefix}Rezerwacje WHERE ID_biletu = ? AND ID_użytkownika = ?";
    $stmt_delete_ticket = mysqli_prepare($conn, $sql_delete_ticket);
    mysqli_stmt_bind_param($stmt_delete_ticket, "ii", $ticket_id, $user_id);
    mysqli_stmt_execute($stmt_delete_ticket);
    header("Location: userprofile.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilety użytkownika</title>
    <link rel="stylesheet" href="css/profiluserBILETY.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="header__wrapper">
        <header></header>
        <div class="cols__container">
            <div class="left__col">
                <div class="img__container">
                    <img src="assets/profile-user-icon-isolated-on-white-background-eps10-free-vector" alt="Zdjecie uzytkownkika" />
                    <span></span>
                </div>
                <h2>Bilety użytkownika: <?php echo $user['Imię'] . ' ' . $user['Nazwisko']; ?></h2>
                <ul class="about">
                </ul>
            </div>
            <div class="right__col">
                <nav>
                    <ul>
                        <li><a href="userprofile.php">Twoje dane</a></li>
                        <li><a href="">Bilety</a></li>
                        <li><a href="user_delete_ticket.php">Usuń bilet</a></li>
                        <li><a href="index.php">Wróć do strony głównej</a></li>
                    </ul>
                </nav>
                <div class="content">
                    <h3>Twoje bilety:</h3>
                    <div class="tickets-container tickets-grid">
                        <ul>
                            <?php while ($row = mysqli_fetch_assoc($result_tickets)): ?>
                                <li>
                                    <strong>Film:</strong> <?php echo $row['Film']; ?><br>
                                    <strong>Data seansu:</strong> <?php echo $row['Data']; ?><br>
                                    <strong>Godzina seansu:</strong> <?php echo $row['Godzina']; ?><br>
                                    <strong>Miejsce:</strong> <?php echo $row['Miejsce']; ?><br>
                                    <strong>Typ biletu:</strong> <?php echo $row['Typ_biletu']; ?><br>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
