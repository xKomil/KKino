<?php
require "config/config.php";

$filmId = $_POST['film'];

$sql = "SELECT ID_seansu, Data, Godzina FROM ${prefix}Seanse WHERE ID_filmu = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $filmId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['ID_seansu'] . '">' . htmlspecialchars($row['Data'] . ' ' . $row['Godzina']) . '</option>';
    }
} else {
    echo ''; // Pusty wynik, jeśli nie ma seansów
}

mysqli_close($conn);
?>
