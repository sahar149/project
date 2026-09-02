<?php
/**
 * Dabberha (دبرها) - UI & View Helper Functions
 * Location: includes/helpers/ui_helpers.php
 */

require_once __DIR__ . '/../translations.php';

/**
 * Render standard status badge for bookings, users, and services.
 *
 * @param string $status
 * @param array $custom_labels
 * @return string HTML
 */
function renderStatusBadge(string $status, array $custom_labels = []): string {
    $status_key = strtolower(trim($status));
    
    $styles = [
        'pending' => 'bg-amber-50 text-amber-700 border border-amber-200/80',
        'confirmed' => 'bg-blue-50 text-blue-700 border border-blue-200/80',
        'in_progress' => 'bg-indigo-50 text-indigo-700 border border-indigo-200/80',
        'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
        'cancelled' => 'bg-rose-50 text-rose-700 border border-rose-200/80',
        'active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
        'inactive' => 'bg-gray-100 text-gray-600 border border-gray-200',
        'fixed' => 'bg-brand-50 text-brand-700 border border-brand-200',
        'hourly' => 'bg-purple-50 text-purple-700 border border-purple-200',
    ];

    $icons = [
        'pending' => 'fa-regular fa-clock',
        'confirmed' => 'fa-solid fa-check',
        'in_progress' => 'fa-solid fa-arrows-rotate',
        'completed' => 'fa-solid fa-circle-check',
        'cancelled' => 'fa-solid fa-xmark',
        'active' => 'fa-solid fa-circle-check',
        'inactive' => 'fa-solid fa-circle-xmark',
        'fixed' => 'fa-solid fa-tag',
        'hourly' => 'fa-regular fa-clock',
    ];

    $badge_style = $styles[$status_key] ?? 'bg-gray-100 text-gray-700 border border-gray-200';
    $icon_class = $icons[$status_key] ?? 'fa-regular fa-circle';
    
    $label = $custom_labels[$status_key] ?? __($status);

    return sprintf(
        '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold %s">
            <i class="%s text-[10px]"></i>
            <span>%s</span>
        </span>',
        htmlspecialchars($badge_style, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($icon_class, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Render flash / alert message box.
 *
 * @param string $message
 * @param string $type ('success', 'error', 'danger', 'warning', 'info')
 * @param bool $auto_dismiss
 * @return string HTML
 */
function renderAlert(string $message, string $type = 'success', bool $auto_dismiss = true): string {
    if (empty($message)) {
        return '';
    }

    $type_normalized = ($type === 'error' || $type === 'danger') ? 'danger' : strtolower($type);

    $styles = [
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'danger' => 'bg-rose-50 border-rose-200 text-rose-800',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    ];

    $icons = [
        'success' => 'fa-solid fa-circle-check text-emerald-500',
        'danger' => 'fa-solid fa-circle-exclamation text-rose-500',
        'warning' => 'fa-solid fa-triangle-exclamation text-amber-500',
        'info' => 'fa-solid fa-circle-info text-blue-500',
    ];

    $style = $styles[$type_normalized] ?? $styles['info'];
    $icon = $icons[$type_normalized] ?? $icons['info'];
    $auto_attr = $auto_dismiss ? 'data-auto-dismiss="true"' : '';

    return sprintf(
        '<div class="alert-box mb-6 p-4 rounded-xl border flex items-center justify-between shadow-xs %s" role="alert" %s>
            <div class="flex items-center gap-3">
                <i class="%s text-lg flex-shrink-0"></i>
                <span class="text-sm font-medium">%s</span>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors p-1" data-dismiss="alert" aria-label="%s">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>',
        htmlspecialchars($style, ENT_QUOTES, 'UTF-8'),
        $auto_attr,
        htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(__($message), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(__('Close'), ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Render visual star rating.
 *
 * @param float|int $rating
 * @param int $max
 * @param string $size_class
 * @return string HTML
 */
function renderStarRating($rating, int $max = 5, string $size_class = 'text-sm'): string {
    $rating = max(0, min($max, (float)$rating));
    $full_stars = floor($rating);
    $has_half = ($rating - $full_stars) >= 0.5;
    $empty_stars = $max - $full_stars - ($has_half ? 1 : 0);

    $html = '<div class="inline-flex items-center gap-0.5 text-amber-400 ' . htmlspecialchars($size_class, ENT_QUOTES, 'UTF-8') . '" aria-label="' . sprintf(__('Rating: %s out of 5'), $rating) . '">';
    
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fa-solid fa-star"></i>';
    }
    if ($has_half) {
        $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="fa-regular fa-star text-gray-300"></i>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Render empty state placeholder box.
 *
 * @param string $icon (FontAwesome class)
 * @param string $title
 * @param string $description
 * @param string $action_url
 * @param string $action_label
 * @return string HTML
 */
function renderEmptyState(string $icon, string $title, string $description = '', string $action_url = '', string $action_label = ''): string {
    $html = sprintf(
        '<div class="text-center py-12 px-6 rounded-2xl border border-dashed border-brand-border bg-brand-surface/50 max-w-lg mx-auto my-6">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-primary flex items-center justify-center mx-auto mb-4">
                <i class="%s text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-brand-900 mb-1">%s</h3>',
        htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(__($title), ENT_QUOTES, 'UTF-8')
    );

    if (!empty($description)) {
        $html .= sprintf('<p class="text-sm text-brand-textMuted mb-5 max-w-sm mx-auto">%s</p>', htmlspecialchars(__($description), ENT_QUOTES, 'UTF-8'));
    }

    if (!empty($action_url) && !empty($action_label)) {
        $html .= sprintf(
            '<a href="%s" class="inline-flex items-center gap-2 bg-brand-primary hover:bg-brand-primaryHover text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors shadow-xs">
                <span>%s</span>
            </a>',
            htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(__($action_label), ENT_QUOTES, 'UTF-8')
        );
    }

    $html .= '</div>';
    return $html;
}

/**
 * Map category name or database icon to a Google Material Symbol name.
 *
 * @param string $category_name
 * @param string $category_icon
 * @return string Material symbol identifier
 */
function getCategoryMaterialSymbol(string $category_name = '', string $category_icon = ''): string {
    $icon_str = trim(mb_strtolower((string)$category_icon, 'UTF-8'));
    
    if (strpos($icon_str, 'car') !== false) return 'car_repair';
    if (strpos($icon_str, 'graduation') !== false || strpos($icon_str, 'book') !== false) return 'school';
    if (strpos($icon_str, 'snowflake') !== false) return 'ac_unit';
    if (strpos($icon_str, 'hammer') !== false) return 'construction';
    if (strpos($icon_str, 'leaf') !== false) return 'yard';
    if (strpos($icon_str, 'laptop') !== false || strpos($icon_str, 'desktop') !== false) return 'devices';
    if (strpos($icon_str, 'faucet') !== false) return 'plumbing';
    if (strpos($icon_str, 'bolt') !== false) return 'electrical_services';
    if (strpos($icon_str, 'broom') !== false) return 'cleaning_services';
    if (strpos($icon_str, 'paint') !== false) return 'format_paint';
    if (strpos($icon_str, 'truck') !== false) return 'local_shipping';
    if (strpos($icon_str, 'wrench') !== false) return 'handyman';

    $cat = trim(mb_strtolower((string)$category_name, 'UTF-8'));

    $map = [
        'plumbing' => 'plumbing', 'سباكة' => 'plumbing', 'سباك' => 'plumbing',
        'electrical' => 'electrical_services', 'كهرباء' => 'electrical_services', 'كهربائي' => 'electrical_services',
        'cleaning' => 'cleaning_services', 'تنظيف' => 'cleaning_services', 'نظافة' => 'cleaning_services',
        'gardening' => 'yard', 'بستنة' => 'yard', 'حدائق' => 'yard', 'زراعة' => 'yard',
        'moving' => 'local_shipping', 'نقل' => 'local_shipping', 'شحن' => 'local_shipping',
        'painting' => 'format_paint', 'دهان' => 'format_paint', 'طلاء' => 'format_paint',
        'car' => 'car_repair', 'mechanic' => 'car_repair', 'سيار' => 'car_repair', 'ميكانيك' => 'car_repair',
        'tutoring' => 'school', 'teaching' => 'school', 'تدريس' => 'school', 'تعليم' => 'school',
        'ac' => 'ac_unit', 'cooling' => 'ac_unit', 'تكييف' => 'ac_unit', 'تبريد' => 'ac_unit',
        'carpentry' => 'construction', 'نجار' => 'construction', 'بناء' => 'construction',
        'tech' => 'devices', 'تقنية' => 'devices', 'كمبيوتر' => 'laptop',
    ];

    foreach ($map as $key => $symbol) {
        if (strpos($cat, $key) !== false) {
            return $symbol;
        }
    }

    return 'handyman';
}

/**
 * Map category name or database icon to a FontAwesome class string.
 *
 * @param string $category_name
 * @param string $category_icon
 * @return string FontAwesome class
 */
function getCategoryFAIcon(string $category_name = '', string $category_icon = ''): string {
    if (!empty($category_icon) && strpos($category_icon, 'fa-') !== false) {
        return $category_icon;
    }

    $cat = trim(mb_strtolower((string)$category_name, 'UTF-8'));

    $map = [
        'plumb' => 'fa-solid fa-faucet-drip', 'سباك' => 'fa-solid fa-faucet-drip',
        'electr' => 'fa-solid fa-bolt', 'كهرب' => 'fa-solid fa-bolt',
        'clean' => 'fa-solid fa-broom', 'نظاف' => 'fa-solid fa-broom', 'تنظيف' => 'fa-solid fa-broom',
        'paint' => 'fa-solid fa-paint-roller', 'دهان' => 'fa-solid fa-paint-roller', 'طلاء' => 'fa-solid fa-paint-roller',
        'car' => 'fa-solid fa-car-side', 'mechanic' => 'fa-solid fa-car-side', 'سيار' => 'fa-solid fa-car-side',
        'tutor' => 'fa-solid fa-graduation-cap', 'teach' => 'fa-solid fa-graduation-cap', 'تعليم' => 'fa-solid fa-graduation-cap', 'تدريس' => 'fa-solid fa-graduation-cap',
        'garden' => 'fa-solid fa-leaf', 'حدائق' => 'fa-solid fa-leaf', 'بستنة' => 'fa-solid fa-leaf',
        'mov' => 'fa-solid fa-truck', 'نقل' => 'fa-solid fa-truck', 'شحن' => 'fa-solid fa-truck',
        'ac' => 'fa-solid fa-snowflake', 'تكييف' => 'fa-solid fa-snowflake', 'تبريد' => 'fa-solid fa-snowflake',
        'carpent' => 'fa-solid fa-hammer', 'نجار' => 'fa-solid fa-hammer',
        'tech' => 'fa-solid fa-laptop-code', 'تقني' => 'fa-solid fa-laptop-code',
    ];

    foreach ($map as $key => $fa_class) {
        if (strpos($cat, $key) !== false) {
            return $fa_class;
        }
    }

    return 'fa-solid fa-wrench';
}
