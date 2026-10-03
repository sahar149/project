<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/navbar_public.php';
require_once __DIR__ . '/../includes/components/footer_public.php';
require_once __DIR__ . '/../includes/db/categories_db.php';

// إلغاء تحديد الموقع
if (isset($_GET['clear_location'])) {
    unset($_SESSION['user_lat'], $_SESSION['user_lng']);
    header('Location: browse-services.php');
    exit;
}

// الإحداثيات الافتراضية لمدينة مصراتة
$misrata_lat = 32.3754;
$misrata_lng = 15.0925;

// قراءة إحداثيات المستخدم (GPS) أو اعتماد موقع مصراتة
$user_lat = (isset($_GET['lat']) && $_GET['lat'] !== '') ? (float)$_GET['lat'] : ($_SESSION['user_lat'] ?? null);
$user_lng = (isset($_GET['lng']) && $_GET['lng'] !== '') ? (float)$_GET['lng'] : ($_SESSION['user_lng'] ?? null);

if (isset($_GET['lat']) && isset($_GET['lng'])) {
    $_SESSION['user_lat'] = $user_lat;
    $_SESSION['user_lng'] = $user_lng;
}

// أوزان الترتيب الذكي (التقييم 60% + القرب 40%)
$rating_weight = 0.6;
$distance_weight = 0.4;

$categories = getAllCategories();
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$view_mode = isset($_GET['view']) && $_GET['view'] === 'map' ? 'map' : 'grid';

// استعلام الخدمات في نطاق مصراتة
$sql = "SELECT s.*, 
        u.name as provider_name, 
        u.phone as provider_phone, 
        u.city as provider_city,
        u.address as provider_address,
        u.latitude as provider_lat, 
        u.longitude as provider_lng,
        c.name as category_name, 
        c.id as category_id,
        COALESCE(ROUND(AVG(r.rating), 1), 0) as avg_rating,
        COUNT(r.id) as review_count
        FROM services s
        JOIN users u ON s.provider_id = u.id
        JOIN categories c ON s.category_id = c.id
        LEFT JOIN reviews r ON r.provider_id = u.id
        WHERE u.status = 'active'";

$params = [];

if ($category_filter > 0) {
    $sql .= " AND s.category_id = :cat_id";
    $params[':cat_id'] = $category_filter;
}

if (!empty($search)) {
    $sql .= " AND (s.title LIKE :search OR s.description LIKE :search OR u.name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " GROUP BY s.id";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$raw_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب المسافات والترتيب التلقائي للأقرب والأعلى تقييماً
$services = [];
$map_markers = [];

// نقطة المرجع لحساب المسافة (موقع المستخدم الحالي أو مركز مصراتة)
$ref_lat = $user_lat ?: $misrata_lat;
$ref_lng = $user_lng ?: $misrata_lng;

foreach ($raw_services as $service) {
    $p_lat = !empty($service['provider_lat']) ? (float)$service['provider_lat'] : $misrata_lat;
    $p_lng = !empty($service['provider_lng']) ? (float)$service['provider_lng'] : $misrata_lng;

    // حساب المسافة
    $distance = calculateHaversineDistance((float)$ref_lat, (float)$ref_lng, (float)$p_lat, (float)$p_lng);

    // حساب النقاط الذكية (تقييم 60% + قرب 40%)
    $rating_component = ($service['review_count'] > 0) ? (float)$service['avg_rating'] : 3.5;
    $distance_component = 5.0 / (1.0 + ($distance / 15.0));
    $smart_score = ($rating_component * $rating_weight) + ($distance_component * $distance_weight);

    $service['effective_lat'] = $p_lat;
    $service['effective_lng'] = $p_lng;
    $service['distance'] = $distance;
    $service['recommendation_score'] = round($smart_score, 2);

    $services[] = $service;

    // علامات الخريطة
    $map_markers[] = [
        'id' => (int)$service['id'],
        'title' => $service['title'],
        'provider' => $service['provider_name'],
        'city' => 'مصراتة',
        'category' => __($service['category_name']),
        'price' => number_format((float)$service['price'], 2),
        'price_type' => __($service['price_type'] ?? 'fixed'),
        'rating' => (float)$service['avg_rating'],
        'reviews' => (int)$service['review_count'],
        'lat' => $p_lat,
        'lng' => $p_lng,
        'distance_str' => formatDistance($distance),
        'url' => 'service-detail.php?id=' . (int)$service['id']
    ];
}

// الترتيب التلقائي دائماً: الأقرب والأعلى تقييماً معاً
usort($services, function ($a, $b) {
    if (abs($b['recommendation_score'] - $a['recommendation_score']) > 0.01) {
        return $b['recommendation_score'] <=> $a['recommendation_score'];
    }
    if ($a['distance'] !== null && $b['distance'] !== null) {
        return $a['distance'] <=> $b['distance'];
    }
    return $b['id'] <=> $a['id'];
});

$extra_head = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.leaflet-popup-content-wrapper {
    border-radius: 1rem;
    padding: 4px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}
.leaflet-popup-content {
    margin: 8px 12px;
    line-height: 1.5;
    font-family: Tajawal, sans-serif;
}
</style>
';

renderHead([
    'title' => __('Browse Services - Dabberha'),
    'extra_head' => $extra_head
]);
renderPublicNavbar(['active_page' => 'services']);
?>

<main class="flex-grow px-4 md:px-8 py-8 max-w-7xl mx-auto w-full">

    <!-- ===================================================== -->
    <!-- رأس الصفحة -->
    <!-- ===================================================== -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-4xl text-primary" style="font-variation-settings: 'FILL' 1;">
                grid_view
            </span>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-on-background">
                    <?php echo __('Browse Services'); ?>
                </h1>
                <p class="text-sm md:text-base text-on-surface-variant mt-1">
                    <?php echo __('Find trusted service providers near you'); ?>
                </p>
            </div>
        </div>

        <!-- أزرار التبديل بين العرضين -->
        <div class="inline-flex p-1 bg-surface-container rounded-xl border border-outline-variant self-start md:self-auto">
            <button type="button" id="toggleGridView" onclick="switchView('grid')" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all <?php echo $view_mode === 'grid' ? 'bg-surface-container-lowest text-primary shadow-xs' : 'text-on-surface-variant hover:text-on-background'; ?>">
                <span class="material-symbols-outlined text-lg">grid_view</span>
                <span><?php echo __('Grid View'); ?></span>
            </button>
            <button type="button" id="toggleMapView" onclick="switchView('map')" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all <?php echo $view_mode === 'map' ? 'bg-surface-container-lowest text-primary shadow-xs' : 'text-on-surface-variant hover:text-on-background'; ?>">
                <span class="material-symbols-outlined text-lg">map</span>
                <span><?php echo __('Map View'); ?></span>
            </button>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- شريط الموقع الذكي (مصراتة / GPS) -->
    <!-- ===================================================== -->
    <?php if ($user_lat && $user_lng): ?>
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <span class="material-symbols-outlined text-primary text-2xl mt-0.5 sm:mt-0">
                    near_me
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-bold text-primary">
                            <?php echo __('Showing services near your location'); ?>
                        </span>
                        <span class="inline-flex items-center gap-1 bg-primary text-on-primary text-xs px-2.5 py-0.5 rounded-full font-bold">
                            <span class="material-symbols-outlined text-xs">my_location</span>
                            <span>موقعك المباشر</span>
                        </span>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-1">
                        مرتبة تلقائياً بحسب <strong class="text-primary">الأقرب جغرافياً والأعلى تقييماً</strong>
                    </p>
                </div>
            </div>
            <a href="browse-services.php?clear_location=1" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-primary/30 text-primary text-xs font-bold hover:bg-primary/10 transition-colors whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">close</span>
                <span><?php echo __('Clear'); ?></span>
            </a>
        </div>
    <?php endif; ?>

    <!-- ===================================================== -->
    <!-- شريط البحث والفلترة -->
    <!-- ===================================================== -->
    <div class="bg-surface-container-lowest ambient-shadow rounded-2xl p-5 md:p-6 mb-8 border border-outline-variant/60">
        <form method="GET" action="browse-services.php" id="searchFilterForm" class="space-y-4">
            <input type="hidden" name="view" id="viewInput" value="<?php echo htmlspecialchars($view_mode); ?>">
            <?php if ($user_lat && $user_lng): ?>
                <input type="hidden" name="lat" value="<?php echo htmlspecialchars((string)$user_lat); ?>">
                <input type="hidden" name="lng" value="<?php echo htmlspecialchars((string)$user_lng); ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <!-- حقل البحث بالاسم والوصف -->
                <div class="md:col-span-7">
                    <div class="relative flex items-center bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <span class="material-symbols-outlined text-on-surface-variant text-xl ml-3 shrink-0 pointer-events-none">
                            search
                        </span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="<?php echo __('Search services...'); ?>" class="w-full bg-transparent text-sm text-on-background outline-none border-none p-0 focus:ring-0">
                    </div>
                </div>

                <!-- قائمة التصنيفات (مع مسافات واضحة تمنع تداخل الأيقونة مع النص تماماً) -->
                <div class="md:col-span-5">
                    <div class="relative flex items-center bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <span class="material-symbols-outlined text-primary text-xl ml-3 shrink-0 pointer-events-none">
                            category
                        </span>
                        <select name="category" class="w-full bg-transparent text-sm text-on-background outline-none border-none p-0 focus:ring-0 cursor-pointer appearance-none">
                            <option value="0"><?php echo __('All Categories'); ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int)$cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(__($cat['name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="material-symbols-outlined text-on-surface-variant pointer-events-none text-lg mr-auto shrink-0">
                            expand_more
                        </span>
                    </div>
                </div>
            </div>

            <!-- أزرار الإجراءات -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-outline-variant/40">
                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-primary text-on-primary px-7 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-primary-container transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-sm">search</span>
                        <span><?php echo __('Search'); ?></span>
                    </button>

                    <a href="browse-services.php" class="border border-outline-variant text-on-surface-variant px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-1.5 hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                        <span><?php echo __('Reset'); ?></span>
                    </a>
                </div>

                <!-- زر تحديد الموقع الجغرافي -->
                <button type="button" onclick="detectUserGPS()" id="gpsButton" class="border border-primary/30 text-primary bg-primary/5 hover:bg-primary/10 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-base">my_location</span>
                    <span><?php echo __('Use My Current Location'); ?></span>
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- العرض الأول: الخريطة التفاعلية -->
    <!-- ===================================================== -->
    <div id="mapViewContainer" class="<?php echo $view_mode === 'map' ? 'block' : 'hidden'; ?> mb-12">
        <div class="bg-surface-container-lowest rounded-2xl p-4 border border-outline-variant ambient-shadow">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">pin_drop</span>
                    <h2 class="text-base font-bold text-on-background">مقدمو الخدمات في مصراتة على الخريطة</h2>
                </div>
                <span class="text-xs text-on-surface-variant">
                    <?php echo count($map_markers); ?> <?php echo __('providers mapped'); ?>
                </span>
            </div>

            <!-- خريطة Leaflet -->
            <div id="servicesMap" class="w-full h-[520px] rounded-xl border border-outline-variant z-10"></div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- العرض الثاني: شبكة البطاقات -->
    <!-- ===================================================== -->
    <div id="gridViewContainer" class="<?php echo $view_mode === 'grid' ? 'block' : 'hidden'; ?>">
        <?php if (count($services) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($services as $service): ?>
                    <?php
                    $description = $service['description'] ?? '';
                    if (mb_strlen($description) > 110) {
                        $description = mb_substr($description, 0, 110) . '...';
                    }

                    $full_stars = 0;
                    if ($service['review_count'] > 0) {
                        $full_stars = round($service['avg_rating']);
                    }
                    $empty_stars = 5 - $full_stars;
                    ?>

                    <!-- بطاقة الخدمة -->
                    <div class="bg-surface-container-lowest rounded-2xl p-6 ambient-shadow flex flex-col h-full border border-outline-variant/60 transition-all hover:-translate-y-1 hover:shadow-md duration-300">
                        
                        <!-- أعلى البطاقة: التصنيف بالعربية فقط -->
                        <div class="flex justify-between items-start mb-3 gap-2">
                            <span class="bg-surface-container text-primary text-xs font-bold px-3 py-1 rounded-full">
                                <?php echo htmlspecialchars(__($service['category_name'])); ?>
                            </span>

                            <?php if (isset($service['distance']) && $service['distance'] !== null): ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                                    <span class="material-symbols-outlined text-[13px]">near_me</span>
                                    <span><?php echo formatDistance($service['distance']); ?></span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- عنوان الخدمة -->
                        <h3 class="text-lg font-bold text-on-background mb-2">
                            <a href="service-detail.php?id=<?php echo $service['id']; ?>" class="hover:text-primary transition-colors">
                                <?php echo htmlspecialchars($service['title']); ?>
                            </a>
                        </h3>

                        <!-- اسم المزود والمدينة -->
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-on-surface-variant mb-3">
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm text-primary">person</span>
                                <span class="font-medium text-on-background"><?php echo htmlspecialchars($service['provider_name']); ?></span>
                            </span>

                            <span class="inline-flex items-center gap-1 bg-surface-container-high px-2 py-0.5 rounded-md text-[11px] font-semibold text-on-surface-variant">
                                <span class="material-symbols-outlined text-[12px]">location_on</span>
                                <span>مصراتة</span>
                            </span>
                        </div>

                        <!-- وصف الخدمة -->
                        <p class="text-xs md:text-sm text-on-surface-variant mb-5 line-clamp-2 leading-relaxed">
                            <?php echo htmlspecialchars($description); ?>
                        </p>

                        <!-- أسفل البطاقة: السعر، التقييم، الزر -->
                        <div class="mt-auto pt-4 border-t border-outline-variant/40 space-y-4">
                            <div class="flex items-center justify-between">
                                <!-- السعر -->
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xl font-bold text-primary">
                                        <?php echo number_format((float)$service['price'], 2); ?>
                                    </span>
                                    <span class="text-xs text-on-surface-variant font-bold">
                                        د.ل / <?php echo htmlspecialchars(__($service['price_type'] ?? 'fixed')); ?>
                                    </span>
                                </div>

                                <!-- التقييم النجوم -->
                                <div class="flex items-center gap-1">
                                    <?php if ($service['review_count'] > 0): ?>
                                        <div class="flex text-amber-500 text-sm">
                                            <?php for ($i = 0; $i < $full_stars; $i++): ?>
                                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                            <?php endfor; ?>
                                            <?php for ($i = 0; $i < $empty_stars; $i++): ?>
                                                <span class="material-symbols-outlined text-sm">star</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-xs text-on-surface-variant font-bold">(<?php echo $service['review_count']; ?>)</span>
                                    <?php else: ?>
                                        <span class="text-[11px] text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">
                                            <?php echo __('No reviews yet'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- زر عرض التفاصيل -->
                            <a href="service-detail.php?id=<?php echo $service['id']; ?>" class="w-full bg-primary text-on-primary py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 hover:bg-primary-container transition-colors shadow-xs">
                                <span class="material-symbols-outlined text-base">visibility</span>
                                <span><?php echo __('View Details'); ?></span>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- حالة عدم وجود نتائج -->
            <div class="bg-surface-container-lowest rounded-2xl p-10 ambient-shadow text-center border border-outline-variant max-w-lg mx-auto my-12">
                <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-4 text-primary">
                    <span class="material-symbols-outlined text-3xl">search_off</span>
                </div>
                <h3 class="text-xl font-bold text-on-background mb-2">
                    <?php echo __('No services found'); ?>
                </h3>
                <p class="text-sm text-on-surface-variant mb-6">
                    <?php echo __('Please check back later or try different filters.'); ?>
                </p>
                <a href="browse-services.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary-container transition-colors">
                    <span class="material-symbols-outlined text-sm">refresh</span>
                    <span><?php echo __('Reset Filters'); ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- ========================================================= -->
<!-- الجافاسكربت الخاص بالخريطة والتفاعلات -->
<!-- ========================================================= -->
<script>
const markersData = <?php echo json_encode($map_markers, JSON_UNESCAPED_UNICODE); ?>;
let mapInstance = null;
let markersLayer = null;

function switchView(view) {
    const gridContainer = document.getElementById('gridViewContainer');
    const mapContainer = document.getElementById('mapViewContainer');
    const gridBtn = document.getElementById('toggleGridView');
    const mapBtn = document.getElementById('toggleMapView');
    const viewInput = document.getElementById('viewInput');

    if (view === 'map') {
        gridContainer.classList.add('hidden');
        mapContainer.classList.remove('hidden');
        viewInput.value = 'map';

        mapBtn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all bg-surface-container-lowest text-primary shadow-xs';
        gridBtn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all text-on-surface-variant hover:text-on-background';

        initServicesMap();
    } else {
        mapContainer.classList.add('hidden');
        gridContainer.classList.remove('hidden');
        viewInput.value = 'grid';

        gridBtn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all bg-surface-container-lowest text-primary shadow-xs';
        mapBtn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all text-on-surface-variant hover:text-on-background';
    }
}

function initServicesMap() {
    if (mapInstance) {
        setTimeout(function() {
            mapInstance.invalidateSize();
        }, 200);
        return;
    }

    const defaultLat = <?php echo $misrata_lat; ?>;
    const defaultLng = <?php echo $misrata_lng; ?>;

    mapInstance = L.map('servicesMap', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(mapInstance);

    markersLayer = L.featureGroup().addTo(mapInstance);

    // إذا كان للمستخدم موقع GPS محدد
    <?php if ($user_lat && $user_lng): ?>
    const userMarkerIcon = L.divIcon({
        className: 'user-pin',
        html: '<div style="background-color:#2563eb; width:20px; height:20px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 0 10px rgba(37,99,235,0.6);"></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });
    L.marker([<?php echo $user_lat; ?>, <?php echo $user_lng; ?>], { icon: userMarkerIcon })
        .bindPopup('<strong>موقعك الحالي في مصراتة</strong>')
        .addTo(markersLayer);
    <?php endif; ?>

    // إضافة مقدمي الخدمات في مصراتة
    if (markersData && markersData.length > 0) {
        markersData.forEach(function(item) {
            const providerIcon = L.divIcon({
                className: 'custom-provider-pin',
                html: '<div style="background-color:#95442b; color:white; width:34px; height:34px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); display:flex; align-items:center; justify-content:center; border:2px solid white; box-shadow:0 3px 6px rgba(0,0,0,0.3);">' +
                        '<i class="fa-solid fa-wrench" style="transform:rotate(45deg); font-size:13px;"></i>' +
                       '</div>',
                iconSize: [34, 34],
                iconAnchor: [17, 34],
                popupAnchor: [0, -30]
            });

            let popupHtml = '<div style="direction:rtl; text-align:right; font-family:Tajawal, sans-serif; min-width:180px;">' +
                '<span style="font-size:11px; background:#f4ebe8; color:#95442b; padding:2px 8px; border-radius:12px; font-weight:bold;">' + item.category + '</span>' +
                '<h4 style="margin:6px 0 2px 0; font-size:14px; font-weight:bold; color:#1f2937;">' + item.title + '</h4>' +
                '<p style="margin:0 0 4px 0; font-size:12px; color:#4b5563;">👤 ' + item.provider + ' (مصراتة)</p>';
            
            if (item.distance_str) {
                popupHtml += '<p style="margin:0 0 6px 0; font-size:11px; color:#95442b; font-weight:bold;">📍 ' + item.distance_str + '</p>';
            }

            popupHtml += '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; border-top:1px solid #eee; padding-top:6px;">' +
                    '<span style="font-weight:bold; font-size:13px; color:#95442b;">' + item.price + ' د.ل / ' + item.price_type + '</span>' +
                    '<a href="' + item.url + '" style="background:#95442b; color:white; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:bold; text-decoration:none;">عرض الخدمة</a>' +
                '</div>' +
            '</div>';

            L.marker([item.lat, item.lng], { icon: providerIcon })
                .bindPopup(popupHtml)
                .addTo(markersLayer);
        });

        if (markersLayer.getLayers().length > 0) {
            mapInstance.fitBounds(markersLayer.getBounds().pad(0.15));
        }
    }

    setTimeout(function() {
        mapInstance.invalidateSize();
    }, 300);
}

// تحديد الموقع التلقائي GPS
function detectUserGPS() {
    const btn = document.getElementById('gpsButton');
    if (!navigator.geolocation) {
        alert('ميزة تحديد الموقع غير مدعومة من متصفحك.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-base">progress_activity</span><span>جارٍ تحديد الموقع...</span>';

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            // إذا كان الموقع المكتشف خارج مصراتة (بسبب اتصال راوتر/كمبيوتر عبر IP طرابلس)
            if (lat < 32.10 || lat > 32.60 || lng < 14.80 || lng > 15.40) {
                alert('تنبيه: مزود خدمة الإنترنت في جهازك (IP) يقع في طرابلس. تم ضبط البحث تلقائياً على مركز مدينة مصراتة لتظهر لك الخدمات القريبة في مدينتك بدقة.');
                window.location.href = 'browse-services.php?lat=32.3754&lng=15.0925';
            } else {
                window.location.href = 'browse-services.php?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng);
            }
        },
        function (err) {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-base">my_location</span><span>استخدام موقعي الحالي</span>';
            alert('تعذر تحديد موقع GPS تلقائياً. تأكد من تفعيل إذن الموقع في المتصفح.');
        },
        { timeout: 10000, enableHighAccuracy: false, maximumAge: 60000 }
    );
}

// تشغيل الخريطة تلقائياً إذا كان نمط الخريطة مفعلاً
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($view_mode === 'map'): ?>
        initServicesMap();
    <?php endif; ?>
});
</script>

<?php renderPublicFooter(['active_page' => 'services']); ?>