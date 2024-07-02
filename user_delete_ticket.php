<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit();
}

require "config/config.php";

$user_id = $_SESSION['user_id'];

// Pobieranie biletów użytkownika
$sql_tickets = "SELECT 
                    ${prefix}Rezerwacje.ID_rezerwacji, ${prefix}Rezerwacje.ID_biletu, ${prefix}Rezerwacje.Miejsce, ${prefix}Seanse.Data, ${prefix}Seanse.Godzina, ${prefix}Filmy.Tytuł AS Film
                FROM ${prefix}Rezerwacje
                JOIN ${prefix}Filmy ON ${prefix}Rezerwacje.ID_filmu = ${prefix}Filmy.ID_filmu
                JOIN ${prefix}Seanse ON ${prefix}Rezerwacje.ID_seansu = ${prefix}Seanse.ID_seansu
                WHERE ${prefix}Rezerwacje.ID_użytkownika = ?";

$stmt_tickets = mysqli_prepare($conn, $sql_tickets);
mysqli_stmt_bind_param($stmt_tickets, "i", $user_id);
mysqli_stmt_execute($stmt_tickets);
$result_tickets = mysqli_stmt_get_result($stmt_tickets);

// Zapis wyników zapytania do tablicy
$tickets = [];
while ($row = mysqli_fetch_assoc($result_tickets)) {
    $tickets[] = $row;
}

// Obsługa usuwania biletu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];
    $sql_delete_ticket = "DELETE FROM ${prefix}Rezerwacje WHERE ID_rezerwacji = ? AND ID_użytkownika = ?";
    $stmt_delete_ticket = mysqli_prepare($conn, $sql_delete_ticket);
    mysqli_stmt_bind_param($stmt_delete_ticket, "ii", $reservation_id, $user_id);
    mysqli_stmt_execute($stmt_delete_ticket);

    // Sprawdzenie, czy usunięcie biletu powiodło się
    if (mysqli_stmt_affected_rows($stmt_delete_ticket) > 0) {
        $message = "Bilet został usunięty pomyślnie.";
    } else {
        $message = "Wystąpił błąd podczas usuwania biletu.";
    }

    mysqli_stmt_close($stmt_delete_ticket);

    // Odświeżenie listy biletów po usunięciu
    mysqli_stmt_execute($stmt_tickets);
    $result_tickets = mysqli_stmt_get_result($stmt_tickets);

    // Zapis nowych wyników do tablicy po usunięciu
    $tickets = [];
    while ($row = mysqli_fetch_assoc($result_tickets)) {
        $tickets[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuń bilet</title>
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
                <h2>Usuń bilet</h2>
                <ul class="about">
                </ul>
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
                <div class="content">
                    <h3>Wybierz bilet do usunięcia:</h3>
                    <?php if (isset($message)): ?>
                        <p><?php echo $message; ?></p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <label for="reservation_id">Bilet:</label>
                        <select name="reservation_id" id="reservation_id">
                            <?php foreach ($tickets as $ticket): ?>
                                <option value="<?php echo htmlspecialchars($ticket['ID_rezerwacji']); ?>">
                                    <?php echo htmlspecialchars($ticket['Film'] . ' - ' . $ticket['Data'] . ' - ' . $ticket['Godzina'] . ' - ' . $ticket['Miejsce']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Usuń bilet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
