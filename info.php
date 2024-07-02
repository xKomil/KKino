<?php
// Importowanie zmiennych konfiguracyjnych
require_once 'config/config.php';

// Połączenie z bazą danych
$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Odczyt wersji MySQL
$sql = "SELECT VERSION() AS version";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mysql_version = $row["version"];
    echo "MySQL version: " . $mysql_version;
} else {
    echo "0 results";
}

$conn->close();
?>
