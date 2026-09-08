<?php
/**
 * Dabberha (دبرها) - Booking Calendar
 * Location: public/booking-calendar.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/components/head.php';

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

renderHead(['title' => __('Booking Calendar') . ' - ' . __('Dabberha')]);
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/ar.js'></script>

<div class="min-h-screen bg-background py-10 px-4 sm:px-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <a href="service-detail.php?id=<?php echo $service_id; ?>" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                <i class="fa-solid fa-arrow-right text-xs"></i>
                <span><?php echo __('Back to Service'); ?></span>
            </a>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-ambient border border-surface-variant overflow-hidden">
            <div class="bg-primary text-white p-6 flex items-center gap-3">
                <i class="fa-regular fa-calendar-check text-2xl"></i>
                <div>
                    <h1 class="text-xl font-bold"><?php echo __('Select Booking Date & Time'); ?></h1>
                    <p class="text-xs text-white/80 mt-0.5"><?php echo __('Choose a convenient appointment from available slots'); ?></p>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div id='calendar' class="rounded-xl overflow-hidden border border-surface-variant p-2"></div>
                <div id="selected-time" class="mt-4"></div>
                <form id="booking-form" method="POST" action="book-service.php">
                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                    <input type="hidden" name="provider_id" value="<?php echo $provider_id; ?>">
                    <input type="hidden" name="booking_date" id="booking_date">
                    <input type="hidden" name="booking_time" id="booking_time">
                    <button type="submit" class="w-full bg-primary hover:bg-[#7a2f18] text-white font-bold py-3.5 rounded-xl text-sm transition-all shadow-ambient flex items-center justify-center gap-2 mt-4 disabled:opacity-50 disabled:cursor-not-allowed" id="book-btn" disabled>
                        <i class="fa-solid fa-check"></i>
                        <span><?php echo __('Confirm Booking'); ?></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var bookedDates = <?php echo json_encode($booked_dates); ?>;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        selectable: true,
        select: function(info) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var selected = new Date(info.startStr);

            if (selected < today) {
                alert('<?php echo __('You can only book for future dates.'); ?>');
                return;
            }
            
            var selectedDate = info.startStr;
            document.getElementById('booking_date').value = selectedDate;
            
            var timeHtml = '<div class="space-y-2"><label class="block text-xs font-bold text-on-background"><?php echo __('Select Time:'); ?></label><select class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary" id="time-select">';
            timeHtml += '<option value=""><?php echo __('Choose Time'); ?></option>';
            for (var h = 8; h <= 20; h++) {
                for (var m = 0; m < 60; m += 30) {
                    var timeStr = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                    var isBooked = bookedDates.some(function(date) {
                        return date.includes(selectedDate + ' ' + timeStr);
                    });
                    var timeVal = timeStr + ':00';
                    if (!isBooked) {
                        timeHtml += '<option value="' + timeVal + '">' + timeStr + '</option>';
                    }
                }
            }
            timeHtml += '</select></div>';
            document.getElementById('selected-time').innerHTML = timeHtml;
            document.getElementById('book-btn').disabled = true;

            document.getElementById('time-select').addEventListener('change', function() {
                document.getElementById('booking_time').value = this.value;
                document.getElementById('book-btn').disabled = !this.value;
            });
        }
    });

    calendar.render();
});
</script>
</body>
</html>
