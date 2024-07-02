<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kup Bilet</title>
    <link rel="stylesheet" href="css/kupowaniebiletow.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="script/updateSeats.js"></script>
</head>
<body>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }

        #miejsce {
            text-align: center;
            margin-bottom: 20px;
        }

        .ekran {
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .row {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .seat {
            width: 30px;
            height: 30px;
            background-color: #ddd;
            border: 1px solid #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }

        .seat.reserved {
            background-color: #ff6347; /* Indian Red */
            color: #fff;
            pointer-events: none; /* Uniemożliwia kliknięcie */
        }

        .seat.selected {
            background-color: #4caf50; /* Green */
            color: #fff;
        }

        .disabled {
            background-color: #ccc;
            pointer-events: none;
        }

        button[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        button[type="submit"].disabled {
            background-color: #ccc;
            cursor: default;
        }

        button[type="submit"].disabled:hover {
            background-color: #ccc;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.8;
        }

        button[type="submit"], button[type="button"] {
            width: 100%;
        }
    </style>
    <div id="form-container">
        <?php
        require "config/config.php";
        session_start();
        

        if (!isset($_SESSION['loggedin'])) {
            header('Location: login.php');
            exit();
        }


        $sql = "SELECT ID_filmu, Tytuł FROM ${prefix}Filmy";
        $result = mysqli_query($conn, $sql);
        $filmy = [];
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $filmy[] = $row;
            }
        }

        $sql = "SELECT ID_biletu, Nazwa_biletu, Cena FROM ${prefix}Bilety";
        $result = mysqli_query($conn, $sql);
        $bilety = [];
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $bilety[] = $row;
            }
        }

        mysqli_close($conn);
        ?>

        <form id="filter-form">
            <label for="film">Wybierz film:</label>
            <select name="film" id="film" onchange="updateSeanse()">
                <option value="">Wybierz film</option>
                <?php foreach ($filmy as $film): ?>
                    <option value="<?php echo $film['ID_filmu']; ?>">
                        <?php echo htmlspecialchars($film['Tytuł']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="seans">Wybierz datę i godzinę:</label>
            <select name="seans" id="seans" onchange="updateSeats()">
                <!-- Opcje będą załadowane dynamicznie przez JavaScript -->
            </select>
            <div id="error-message" class="error"></div>
        </form>
        
        <form action="rezerwuj.php" method="post" onsubmit="return validateForm()">
            <input type="hidden" name="film" value="" id="selectedFilm">
            <input type="hidden" name="seans" value="" id="selectedSeans">
            
            <label for="bilet">Wybierz rodzaj biletu:</label>
            <select name="bilet" id="bilet">
                <?php foreach ($bilety as $bilet): ?>
                    <option value="<?php echo $bilet['ID_biletu']; ?>"><?php echo htmlspecialchars($bilet['Nazwa_biletu'] . ' (' . $bilet['Cena'] . ' zł)'); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label for="miejsce">Wybierz miejsca:</label>
            <div id="miejsce">
                <div class="ekran">EKRAN</div>
                <!-- Miejsca będą załadowane dynamicznie przez JavaScript -->
            </div>
            
            <input type="hidden" name="wybrane_miejsca" id="wybrane_miejsca">
            
            <button type="submit" id="rezerwujBtn" class="disabled" disabled>Rezerwuj</button>
        </form>

        <form action="index.php" method="post">
            <button type="submit">Wróć</button>
        </form>
    </div>

    <script>
        function updateSeanse() {
            console.log("Wywołano funkcję updateSeanse");
            var filmId = $('#film').val();
            if (filmId === "") {
                $('#seans').html('<option value="">Wybierz datę i godzinę</option>');
                $('#miejsce').html('<div class="ekran">EKRAN</div>');
                $('#error-message').html('');
                $('#rezerwujBtn').addClass('disabled').prop('disabled', true);
                return;
            }

            $.ajax({
                url: 'get_seanse.php',
                type: 'POST',
                data: { film: filmId },
                success: function(data) {
                    if (data.trim() === "") {
                        $('#error-message').html('Brak dostępnych seansów dla wybranego filmu.');
                        $('#seans').html('<option value="">Brak dostępnych seansów</option>');
                        // Wyświetlenie miejsc na czerwono
                        $('#miejsce').html('<div class="ekran">EKRAN</div>');
                        for (var i = 0; i < 5; i++) { // Zakładam, że mamy 5 rzędów i 12 miejsc w każdym rzędzie
                            for (var j = 1; j <= 12; j++) {
                                $('#miejsce').append('<div class="seat reserved" data-miejsce="' + String.fromCharCode(65 + i) + j + '">X</div>');
                            }
                            $('#miejsce').append('<br/>');
                        }
                        $('#rezerwujBtn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#error-message').html('');
                        $('#seans').html(data);
                        updateSeats();
                    }
                }
            });
        }

        function updateSeats() {
            var seansId = $('#seans').val();
            var filmId = $('#film').val();
            if (seansId === "") {
                $('#miejsce').html('<div class="ekran">EKRAN</div>');
                $('#rezerwujBtn').addClass('disabled').prop('disabled', true);
                return;
            }

            $('#selectedFilm').val(filmId);
            $('#selectedSeans').val(seansId);
            $.ajax({
                url: 'get_seats.php',
                type: 'POST',
                data: { film: filmId, seans: seansId },
                success: function(data) {
                    $('#miejsce').html('<div class="ekran">EKRAN</div>' + data);
                    $('#rezerwujBtn').removeClass('disabled').prop('disabled', true);
                }
            });
        }

        function selectSeat(seat) {
            if (seat.classList.contains('reserved')) {
                return;
            }

            seat.classList.toggle('selected');

            var selectedSeats = [];
            var seats = document.querySelectorAll('.seat.selected');
            seats.forEach(function(seat) {
                selectedSeats.push(seat.dataset.miejsce);
            });

            document.getElementById('wybrane_miejsca').value = selectedSeats.join(',');

            // Aktywacja/deaktywacja przycisku "Rezerwuj"
            if (selectedSeats.length > 0) {
                $('#rezerwujBtn').removeClass('disabled').prop('disabled', false);
            } else {
                $('#rezerwujBtn').addClass('disabled').prop('disabled', true);
            }
        }

        function validateForm() {
            var selectedSeats = document.getElementById('wybrane_miejsca').value;
            if (selectedSeats === "") {
                alert("Proszę wybrać miejsce.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
