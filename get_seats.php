<?php
require_once "config/config.php";

$filmId = $_POST['film'];
$seansId = $_POST['seans'];

function getReservedSeats($conn, $filmId, $seansId) {
    global $prefix; // Dodane, aby uzyskać dostęp do zmiennej prefiksu z pliku konfiguracyjnego
    $sql = "SELECT Miejsce FROM ${prefix}Rezerwacje WHERE ID_filmu = $filmId AND ID_seansu = $seansId";
    $result = mysqli_query($conn, $sql);
    $zarezerwowaneMiejsca = [];
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $zarezerwowaneMiejsca[] = $row['Miejsce'];
        }
    }
    return $zarezerwowaneMiejsca;
}

$zarezerwowaneMiejsca = getReservedSeats($conn, $filmId, $seansId);

$rows = ['A', 'B', 'C', 'D', 'E', 'F'];
$cols = 10;
foreach ($rows as $row) {
    echo '<div class="row">';
    for ($col = 1; $col <= $cols; $col++) {
        $miejsce = $row . $col;
        $class = in_array($miejsce, $zarezerwowaneMiejsca) ? 'reserved' : '';
        echo '<div class="seat ' . $class . '" data-miejsce="' . $miejsce . '" onclick="selectSeat(this)">' . $miejsce . '</div>';
    }
    echo '</div>';
}

mysqli_close($conn);
?>
