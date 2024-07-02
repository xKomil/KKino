<?php
require_once "config/config.php";
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $film_id = $_POST['film'];
    $bilet_id = $_POST['bilet'];
    $seans_id = $_POST['seans'];
    $miejsca = explode(',', $_POST['wybrane_miejsca']);

    if (empty($seans_id)) {
        echo "Nie wybrano seansu. Proszę wybrać seans.";
        exit();
    }

    foreach ($miejsca as $miejsce) {
        // Sprawdzenie, czy wybrane miejsce jest już zarezerwowane
        $sql_check_reserved = "SELECT * FROM ${prefix}Rezerwacje WHERE ID_seansu = ? AND ID_filmu = ? AND Miejsce = ?";
        $stmt_check_reserved = mysqli_prepare($conn, $sql_check_reserved);
        mysqli_stmt_bind_param($stmt_check_reserved, "iis", $seans_id, $film_id, $miejsce);
        mysqli_stmt_execute($stmt_check_reserved);
        mysqli_stmt_store_result($stmt_check_reserved);
        $rows = mysqli_stmt_num_rows($stmt_check_reserved);

        if ($rows > 0) {
            echo "Miejsce $miejsce jest już zarezerwowane. Wybierz inne.";
            exit();
        } else {
            // Dodanie rezerwacji
            $sql_insert_reservation = "INSERT INTO ${prefix}Rezerwacje (ID_użytkownika, ID_filmu, ID_biletu, ID_seansu, Miejsce) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert_reservation = mysqli_prepare($conn, $sql_insert_reservation);
            mysqli_stmt_bind_param($stmt_insert_reservation, "iiiss", $user_id, $film_id, $bilet_id, $seans_id, $miejsce);

            if (!mysqli_stmt_execute($stmt_insert_reservation)) {
                echo "Error: " . $sql_insert_reservation . "<br>" . mysqli_error($conn);
                exit();
            }
        }
    }

    mysqli_close($conn);
    header('Location: userprofileBILETY.php');
    exit();
}
?>
