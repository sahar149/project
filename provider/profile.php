<?php
/**
 * Dabberha (دبرها) - Provider Profile & Geolocation Settings
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
$libyan_cities = getLibyanCities();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
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

$default_lat = $user['latitude'] ? (float)$user['latitude'] : 32.8872;
$default_lng = $user['longitude'] ? (float)$user['longitude'] : 13.1913;
$has_saved_location = !empty($user['latitude']) && !empty($user['longitude']);

$extra_head = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                <p class="text-brand-textMuted mt-1"><?php echo __('Manage your personal contact details, city, and exact service location on the map'); ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <?php echo renderAlert($error, 'danger'); ?>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <?php echo renderAlert($success, 'success'); ?>
            <?php endif; ?>

            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-brand-border">
                <form method="POST" class="space-y-6">
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
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" placeholder="+218 91 000 0000">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="city-select">
                                <?php echo __('City / Region'); ?> <span class="text-brand-danger">*</span>
                            </label>
                            <select name="city" id="city-select" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                <option value=""><?php echo __('Select City'); ?></option>
                                <?php foreach ($libyan_cities as $c_key => $c_data): ?>
                                    <option value="<?php echo htmlspecialchars($c_data['name']); ?>" 
                                            data-lat="<?php echo $c_data['lat']; ?>" 
                                            data-lng="<?php echo $c_data['lng']; ?>"
                                            <?php echo (($user['city'] ?? '') === $c_data['name'] || strpos(($user['address'] ?? ''), $c_data['name']) !== false) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c_data['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-brand-text mb-2" for="address">
                            <?php echo __('Detailed Address / Neighborhood'); ?>
                        </label>
                        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" placeholder="<?php echo __('e.g., Tripoli - Ben Ashour, near Al-Madina Mall'); ?>">
                    </div>

                    <!-- Interactive Location Map Section -->
                    <div class="pt-4 border-t border-brand-border space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label class="block text-sm font-bold text-brand-text flex items-center gap-2">
                                    <i class="fa-solid fa-map-location-dot text-brand-primary"></i>
                                    <span><?php echo __('Location on Map (for proximity calculations)'); ?></span>
                                </label>
                                <p class="text-xs text-brand-textMuted"><?php echo __('Click on the map or drag the marker to your workplace or service coverage area'); ?></p>
                            </div>

                            <button type="button" id="btn-locate-me" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-primaryLight hover:bg-brand-primary/20 text-brand-primary text-xs font-bold transition-all shadow-2xs">
                                <i class="fa-solid fa-crosshairs"></i>
                                <span><?php echo __('Use My Current GPS Location'); ?></span>
                            </button>
                        </div>

                        <!-- Map Container -->
                        <div id="provider-map" class="w-full h-72 rounded-2xl border border-brand-border shadow-inner z-10 overflow-hidden"></div>

                        <!-- Hidden and Visible Coordinates -->
                        <input type="hidden" name="latitude" id="latitude-input" value="<?php echo htmlspecialchars($user['latitude'] ?? ''); ?>">
                        <input type="hidden" name="longitude" id="longitude-input" value="<?php echo htmlspecialchars($user['longitude'] ?? ''); ?>">

                        <div class="flex items-center justify-between bg-brand-surface/60 border border-brand-border px-4 py-2.5 rounded-xl text-xs">
                            <span class="text-brand-textMuted flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-brand-primary"></i>
                                <span><?php echo __('Coordinates'); ?>:</span>
                                <strong id="coords-display" class="font-mono text-brand-900">
                                    <?php echo $has_saved_location ? number_format((float)$user['latitude'], 6) . ', ' . number_format((float)$user['longitude'], 6) : __('Not set yet (Choose city or click map)'); ?>
                                </strong>
                            </span>
                            <span id="coords-badge" class="px-2 py-0.5 rounded-md font-bold text-[11px] <?php echo $has_saved_location ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?>">
                                <?php echo $has_saved_location ? __('Location Saved') : __('Click map to set'); ?>
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
    const coordsDisplay = document.getElementById('coords-display');
    const coordsBadge = document.getElementById('coords-badge');
    const citySelect = document.getElementById('city-select');
    const locateBtn = document.getElementById('btn-locate-me');

    let initialLat = <?php echo $default_lat; ?>;
    let initialLng = <?php echo $default_lng; ?>;
    let hasSavedLocation = <?php echo $has_saved_location ? 'true' : 'false'; ?>;

    // Initialize Leaflet Map
    const map = L.map('provider-map').setView([initialLat, initialLng], hasSavedLocation ? 14 : 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add Draggable Marker
    let marker = L.marker([initialLat, initialLng], {
        draggable: true
    }).addTo(map);

    marker.bindPopup('<?php echo __('Drag this marker or click anywhere on the map to set your location'); ?>').openPopup();

    function updateCoordinates(lat, lng, isAuto = false) {
        lat = parseFloat(lat).toFixed(6);
        lng = parseFloat(lng).toFixed(6);

        latInput.value = lat;
        lngInput.value = lng;
        coordsDisplay.textContent = lat + ', ' + lng;

        coordsBadge.className = 'px-2 py-0.5 rounded-md font-bold text-[11px] bg-emerald-100 text-emerald-800';
        coordsBadge.textContent = '<?php echo __('Location Selected'); ?>';

        marker.setLatLng([lat, lng]);
        if (!isAuto) {
            map.panTo([lat, lng]);
        }
    }

    // Marker drag event
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng, true);
    });

    // Map click event
    map.on('click', function(e) {
        updateCoordinates(e.latlng.lat, e.latlng.lng);
        marker.openPopup();
    });

    // City select change event
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const selectedOpt = citySelect.options[citySelect.selectedIndex];
            const cityLat = selectedOpt.dataset.lat;
            const cityLng = selectedOpt.dataset.lng;

            if (cityLat && cityLng) {
                map.setView([parseFloat(cityLat), parseFloat(cityLng)], 13);
                updateCoordinates(cityLat, cityLng);
            }
        });
    }

    // Locate me button (GPS)
    if (locateBtn) {
        locateBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                locateBtn.disabled = true;
                locateBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('Locating...'); ?>';

                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.setView([lat, lng], 15);
                    updateCoordinates(lat, lng);

                    locateBtn.disabled = false;
                    locateBtn.innerHTML = '<i class="fa-solid fa-check text-green-600"></i> <?php echo __('Located Successfully'); ?>';
                    setTimeout(() => {
                        locateBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> <?php echo __('Use My Current GPS Location'); ?>';
                    }, 3000);
                }, function(err) {
                    locateBtn.disabled = false;
                    locateBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> <?php echo __('Use My Current GPS Location'); ?>';
                    
                    let msg = 'تعذر تحديد موقعك عبر GPS.';
                    if (err.code === 1) {
                        msg = 'تم رفض إذن الوصول للموقع في المتصفح. يمكنك النقر مباشرة على الخريطة لتحديد مكانك أو اختيار المدينة من القائمة.';
                    } else if (err.code === 2) {
                        msg = 'خدمة الموقع غير متوفرة على هذا الجهاز. يمكنك النقر على الخريطة لتحديد موقعك بكل سهولة.';
                    } else if (err.code === 3) {
                        msg = 'استغرق تحديد الموقع وقتاً طويلاً. يمكنك النقر مباشرة على الخريطة لتحديد مكانك.';
                    }
                    alert(msg);
                }, { timeout: 10000, enableHighAccuracy: false, maximumAge: 60000 });
            } else {
                alert('<?php echo __('Geolocation is not supported by your browser.'); ?>');
            }
        });
    }

    // Invalidate map size to prevent rendering glitch inside flexbox
    setTimeout(function() {
        map.invalidateSize();
    }, 200);
});
</script>
</body>
</html>