<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$provider_id = isset($_GET['provider_id']) ? (int)$_GET['provider_id'] : 0;
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if (!$provider_id || !$service_id) {
    header('Location: browse-services.php');
    exit;
}

// جلب الحجوزات الموجودة للمزود
$stmt = $pdo->prepare("SELECT booking_date, booking_time, status FROM bookings WHERE provider_id = ? AND service_id = ? AND status != 'cancelled'");
$stmt->execute([$provider_id, $service_id]);
$booked_slots = $stmt->fetchAll();

// تحويل الحجوزات إلى مصفوفة للـ JavaScript
$booked_dates = [];
foreach ($booked_slots as $slot) {
    $booked_dates[] = $slot['booking_date'] . ' ' . $slot['booking_time'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Calendar</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Select Booking Date & Time</h4>
            </div>
            <div class="card-body">
                <div id='calendar'></div>
                <div id="selected-time" class="mt-3"></div>
                <form id="booking-form" method="POST" action="book-service.php">
                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                    <input type="hidden" name="provider_id" value="<?php echo $provider_id; ?>">
                    <input type="hidden" name="booking_date" id="booking_date">
                    <input type="hidden" name="booking_time" id="booking_time">
                    <button type="submit" class="btn btn-success w-100 mt-3" id="book-btn" disabled>
                        Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var bookedDates = <?php echo json_encode($booked_dates); ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            selectable: true,
            select: function(info) {
                if (info.start < new Date()) {
                    alert('You can only book for future dates.');
                    return;
                }
                
                var selectedDate = info.startStr;
                document.getElementById('booking_date').value = selectedDate;
                
                var timeHtml = '<div class="mt-2"><label>Select Time:</label><select class="form-select" id="time-select">';
                for (var h = 8; h <= 20; h++) {
                    for (var m = 0; m < 60; m += 30) {
                        var timeStr = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                        var isBooked = bookedDates.some(function(date) {
                            return date.includes(selectedDate + ' ' + timeStr);
                        });
                        timeStr += ':00';
                        if (!isBooked) {
                            timeHtml += '<option value="' + timeStr + '">' + timeStr + '</option>';
                        }
                    }
                }
                timeHtml += '</select></div>';
                document.getElementById('selected-time').innerHTML = timeHtml;
                document.getElementById('book-btn').disabled = false;

                document.getElementById('time-select').addEventListener('change', function() {
                    document.getElementById('booking_time').value = this.value;
                    document.getElementById('book-btn').disabled = false;
                });
            }
        });

        calendar.render();
    });
    </script>
</body>
</html>
