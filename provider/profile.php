<?php
/**
 * Dabberha (دبرها) - Provider Profile & Geolocation Settings (Misrata)
 * Location: provider/profile.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/db/users_db.php';

requireRole('provider');

$provider_id = getUserId();
$user = getUserById($provider_id);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = 'مصراتة';
    $address = trim($_POST['address'] ?? '');
    $latitude = (isset($_POST['latitude']) && $_POST['latitude'] !== '') ? (float)$_POST['latitude'] : null;
    $longitude = (isset($_POST['longitude']) && $_POST['longitude'] !== '') ? (float)$_POST['longitude'] : null;

    if (empty($name)) {
        $error = __('Name is required');
    } else {
        $update_data = [
            'name' => $name,
            'phone' => $phone,
            'city' => $city,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude
        ];

        if (updateUserProfile($provider_id, $update_data)) {
            $success = __('Profile updated successfully!');
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['city'] = $city;
            $user['address'] = $address;
            $user['latitude'] = $latitude;
            $user['longitude'] = $longitude;
        } else {
            $error = __('Failed to update profile.');
        }
    }
}

// مركز مدينة مصراتة الافتراضي
$default_lat = $user['latitude'] ? (float)$user['latitude'] : 32.3754;
$default_lng = $user['longitude'] ? (float)$user['longitude'] : 15.0925;
$has_saved_location = !empty($user['latitude']) && !empty($user['longitude']);

$extra_head = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.leaflet-control-attribution { display: none !important; }
.leaflet-popup-content-wrapper {
    border-radius: 1rem;
    padding: 4px;
}
.leaflet-popup-content {
    margin: 8px 12px;
    font-family: Tajawal, sans-serif;
    font-size: 12px;
    direction: rtl;
    text-align: right;
}
</style>
';

renderHead([
    'title' => __('Edit Profile - Provider') . ' - ' . __('Dabberha'),
    'extra_head' => $extra_head
]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-4xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                    <i class="fa-regular fa-user text-brand-primary"></i>
                    <span><?php echo __('My Profile & Settings'); ?></span>
                </h1>
                <p class="text-brand-textMuted mt-1">إدارة بيانات الاتصال، والحي، وتحديد موقعك الدقيق على خريطة مصراتة</p>
            </div>

            <?php if (!empty($error)): ?>
                <?php echo renderAlert($error, 'danger'); ?>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <?php echo renderAlert($success, 'success'); ?>
            <?php endif; ?>

            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-brand-border">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="city" value="مصراتة">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="name">
                                <?php echo __('Full Name'); ?> <span class="text-brand-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="email">
                                <?php echo __('Email Address'); ?>
                            </label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-gray-100 text-gray-500 text-sm font-medium cursor-not-allowed" disabled>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="phone">
                                <?php echo __('Phone Number'); ?>
                            </label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" placeholder="0910000000">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="address">
                                الحي أو المنطقة في مصراتة
                            </label>
                            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" placeholder="مثال: الرعيضات، رأس الطوبة، قصر أحمد، الرويسات...">
                        </div>
                    </div>

                    <!-- خريطة تحديد الموقع في مصراتة -->
                    <div class="pt-4 border-t border-brand-border space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <label class="block text-sm font-bold text-brand-text flex items-center gap-2">
                                    <i class="fa-solid fa-map-location-dot text-brand-primary"></i>
                                    <span>الموقع الجغرافي على خريطة مصراتة</span>
                                </label>
                                <p class="text-xs text-brand-textMuted mt-0.5">انقر على الخريطة أو اختر حيك لتثبيت موقع خدماتك بدقة</p>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" id="btn-misrata-center" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-bold transition-all shadow-2xs">
                                    <i class="fa-solid fa-location-dot text-amber-700"></i>
                                    <span>مركز مصراتة</span>
                                </button>
                                <button type="button" id="btn-locate-me" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-primaryLight hover:bg-brand-primary/20 text-brand-primary text-xs font-bold transition-all shadow-2xs">
                                    <i class="fa-solid fa-crosshairs"></i>
                                    <span><?php echo __('Use My Current GPS Location'); ?></span>
                                </button>
                            </div>
                        </div>

                        <!-- أزرار سريعة لأحياء مصراتة -->
                        <div class="bg-brand-surface/70 p-3 rounded-xl border border-brand-border">
                            <span class="text-xs font-bold text-brand-textMuted block mb-2">أحياء ومناطق مصراتة (اختيار سريع):</span>
                            <div class="flex flex-wrap gap-1.5" id="neighborhood-buttons">
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3754" data-lng="15.0925" data-name="وسط المدينة">وسط المدينة</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3786" data-lng="15.0988" data-name="الرعيضات">الرعيضات</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3880" data-lng="15.2050" data-name="قصر أحمد">قصر أحمد</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3820" data-lng="15.0850" data-name="رأس الطوبة">رأس الطوبة</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3680" data-lng="15.1050" data-name="الرويسات">الرويسات</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3550" data-lng="15.1180" data-name="الزروق">الزروق</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3150" data-lng="15.1500" data-name="طمينة">طمينة</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3600" data-lng="15.0500" data-name="الغيران">الغيران</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3950" data-lng="15.0750" data-name="الجزيرة">الجزيرة</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3650" data-lng="15.0850" data-name="البيرة">البيرة</button>
                                <button type="button" class="btn-zone px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-brand-primary hover:text-white border border-brand-border transition-colors shadow-2xs" data-lat="32.3500" data-lng="15.0700" data-name="المقاصبة">المقاصبة</button>
                            </div>
                        </div>

                        <!-- عنصر الخريطة -->
                        <div id="provider-map" class="w-full h-80 rounded-2xl border border-brand-border shadow-inner z-10 overflow-hidden"></div>

                        <!-- حقول الإحداثيات المخفية والمعروضة -->
                        <input type="hidden" name="latitude" id="latitude-input" value="<?php echo htmlspecialchars($user['latitude'] ?? ''); ?>">
                        <input type="hidden" name="longitude" id="longitude-input" value="<?php echo htmlspecialchars($user['longitude'] ?? ''); ?>">

                        <div class="flex items-center justify-between bg-brand-surface/60 border border-brand-border px-4 py-2.5 rounded-xl text-xs">
                            <span class="text-brand-textMuted flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-brand-primary"></i>
                                <span><?php echo __('Coordinates'); ?>:</span>
                                <strong id="coords-display" class="font-mono text-brand-900">
                                    <?php echo $has_saved_location ? number_format((float)$user['latitude'], 6) . ', ' . number_format((float)$user['longitude'], 6) : '32.375400, 15.092500 (مصراتة)'; ?>
                                </strong>
                            </span>
                            <span id="coords-badge" class="px-2 py-0.5 rounded-md font-bold text-[11px] <?php echo $has_saved_location ? 'bg-emerald-100 text-emerald-800' : 'bg-brand-primaryLight text-brand-primary'; ?>">
                                <?php echo $has_saved_location ? __('Location Saved') : 'محدد في مصراتة'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-brand-border flex justify-end">
                        <button type="submit" class="bg-brand-primary hover:bg-brand-primaryHover text-white px-8 py-3 rounded-xl text-sm font-bold transition-all shadow-ambient flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span><?php echo __('Save Profile & Location'); ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const latInput = document.getElementById('latitude-input');
    const lngInput = document.getElementById('longitude-input');
    const addressInput = document.getElementById('address');
    const coordsDisplay = document.getElementById('coords-display');
    const coordsBadge = document.getElementById('coords-badge');
    const locateBtn = document.getElementById('btn-locate-me');
    const centerBtn = document.getElementById('btn-misrata-center');

    // إحداثيات مصراتة الافتراضية
    const MISRATA_CENTER_LAT = 32.3754;
    const MISRATA_CENTER_LNG = 15.0925;

    let initialLat = <?php echo $default_lat; ?>;
    let initialLng = <?php echo $default_lng; ?>;
    
    // فحص إذا كانت الإحداثيات السابقة خارج مصراتة (مثلاً طرابلس)
    if (initialLat < 32.0 || initialLat > 32.6 || initialLng < 14.8 || initialLng > 15.4) {
        initialLat = MISRATA_CENTER_LAT;
        initialLng = MISRATA_CENTER_LNG;
    }

    // تهيئة خريطة Leaflet لمصراتة
    const map = L.map('provider-map', {
        attributionControl: false
    }).setView([initialLat, initialLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    // إضافة مؤشر قابل للسحب
    let marker = L.marker([initialLat, initialLng], {
        draggable: true
    }).addTo(map);

    marker.bindPopup('اسحب هذا المؤشر أو انقر في أي مكان على خريطة مصراتة لتثبيت موقعك').openPopup();

    function updateCoordinates(lat, lng, isAuto = false) {
        lat = parseFloat(lat).toFixed(6);
        lng = parseFloat(lng).toFixed(6);

        latInput.value = lat;
        lngInput.value = lng;
        coordsDisplay.textContent = lat + ', ' + lng;

        coordsBadge.className = 'px-2 py-0.5 rounded-md font-bold text-[11px] bg-emerald-100 text-emerald-800';
        coordsBadge.textContent = 'تم تحديد الموقع في مصراتة';

        marker.setLatLng([lat, lng]);
        if (!isAuto) {
            map.panTo([lat, lng]);
        }
    }

    // حدث سحب المؤشر
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng, true);
    });

    // حدث النقر على الخريطة
    map.on('click', function(e) {
        updateCoordinates(e.latlng.lat, e.latlng.lng);
        marker.openPopup();
    });

    // أزرار الأحياء السريعة في مصراتة
    document.querySelectorAll('.btn-zone').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const zLat = parseFloat(this.getAttribute('data-lat'));
            const zLng = parseFloat(this.getAttribute('data-lng'));
            const zName = this.getAttribute('data-name');

            map.setView([zLat, zLng], 15);
            updateCoordinates(zLat, zLng);
            if (addressInput) {
                addressInput.value = zName;
            }
        });
    });

    // زر مركز مصراتة
    if (centerBtn) {
        centerBtn.addEventListener('click', function() {
            map.setView([MISRATA_CENTER_LAT, MISRATA_CENTER_LNG], 14);
            updateCoordinates(MISRATA_CENTER_LAT, MISRATA_CENTER_LNG);
            if (addressInput && !addressInput.value) {
                addressInput.value = 'وسط المدينة';
            }
        });
    }

    // زر تحديد الموقع GPS الذكي
    if (locateBtn) {
        locateBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                locateBtn.disabled = true;
                locateBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('Locating...'); ?>';

                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    // فحص إذا كان الموقع ضمن نطاق مدينة مصراتة
                    const isInMisrata = (lat >= 32.10 && lat <= 32.60 && lng >= 14.80 && lng <= 15.40);

                    locateBtn.disabled = false;
                    locateBtn.innerHTML = '<i class="fa-solid fa-check text-green-600"></i> تم التحديد';
                    setTimeout(() => {
                        locateBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> <?php echo __('Use My Current GPS Location'); ?>';
                    }, 3000);

                    if (isInMisrata) {
                        map.setView([lat, lng], 15);
                        updateCoordinates(lat, lng);
                    } else {
                        // إشعار ودّي بأن مزود الإنترنت يعطي عنوان طرابلس
                        map.setView([MISRATA_CENTER_LAT, MISRATA_CENTER_LNG], 14);
                        updateCoordinates(MISRATA_CENTER_LAT, MISRATA_CENTER_LNG);
                        alert('ملاحظة: جهازك متصل عبر شبكة إنترنت (مزود خدمة IP) تابعة لطرابلس. بما أن منصة دبرها مخصصة لمصراتة، تم توجيه الخريطة لمصراتة لتتمكن من اختيار حيك مباشرة من الخريطة أو الأزرار السريعة.');
                    }
                }, function(err) {
                    locateBtn.disabled = false;
                    locateBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> <?php echo __('Use My Current GPS Location'); ?>';
                    
                    let msg = 'تعذر الوصول للموقع عبر GPS.';
                    if (err.code === 1) {
                        msg = 'تم رفض إذن الموقع في المتصفح. يمكنك النقر مباشرة على الخريطة أو اختيار حيك من الأزرار السريعة.';
                    } else if (err.code === 2) {
                        msg = 'خدمة GPS غير متوفرة على هذا الجهاز. يمكنك النقر مباشرة على الخريطة لاختيار موقعك في مصراتة.';
                    }
                    alert(msg);
                }, { timeout: 10000, enableHighAccuracy: false, maximumAge: 60000 });
            } else {
                alert('ميزة تحديد الموقع غير مدعومة من متصفحك. يمكنك النقر مباشرة على الخريطة.');
            }
        });
    }

    setTimeout(function() {
        map.invalidateSize();
    }, 200);
});
</script>
</body>
</html>