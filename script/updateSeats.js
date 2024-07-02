document.addEventListener("DOMContentLoaded", function() {
    updateSeats();
});

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
            $('#miejsce').html(data); // Wstawienie generowanych miejsc do elementu #miejsce
            $('#rezerwujBtn').addClass('disabled').prop('disabled', true); // Dodane zabezpieczenie przycisku "Rezerwuj"
        }
    });
}


function updateReservedSeats(reservedSeats) {
    var seats = document.querySelectorAll(".seat");
    seats.forEach(seat => {
        var seatNumber = seat.dataset.miejsce;
        if (reservedSeats.includes(seatNumber)) {
            seat.classList.add("reserved");
            seat.classList.remove("selected");
            seat.onclick = null; // Usuń obsługę kliknięcia dla zajętych miejsc
        } else {
            seat.classList.remove("reserved");
            seat.onclick = function() {
                seats.forEach(s => s.classList.remove("selected"));
                this.classList.add("selected");
                document.getElementById("wybrane_miejsce").value = this.dataset.miejsce;
            };
        }
    });
}

function selectSeat(seat) {
    var seats = document.querySelectorAll('.seat');
    seats.forEach(s => s.classList.remove('selected'));
    seat.classList.add('selected');
    document.getElementById("wybrane_miejsce").value = seat.dataset.miejsce;
}
