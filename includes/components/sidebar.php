<?php
/**
 * Dabberha (دبرها) - Universal Dashboard Sidebar Component
 * Location: includes/components/sidebar.php
 *
 * @param string $role 'admin' | 'provider'
 * @param string $active_page (optional: override current active page)
 */

function renderSidebar(string $role = 'provider', string $active_page = ''): void {
    if (empty($active_page)) {
        $active_page = basename($_SERVER['PHP_SELF']);
    }

    $current_status = $_GET['status'] ?? '';

    if ($role === 'admin') {
        $menu_items = [
            [
                'id' => 'dashboard.php',
                'label' => __('Dashboard'),
                'url' => 'dashboard.php',
                'icon' => 'fa-solid fa-chart-pie',
                'is_active' => ($active_page === 'dashboard.php')
            ],
            [
                'id' => 'users.php',
                'label' => __('Users'),
                'url' => 'users.php',
                'icon' => 'fa-solid fa-users',
                'is_active' => ($active_page === 'users.php')
            ],
            [
                'id' => 'services.php',
                'label' => __('Services'),
                'url' => 'services.php',
                'icon' => 'fa-solid fa-briefcase',
                'is_active' => ($active_page === 'services.php')
            ],
            [
                'id' => 'bookings.php',
                'label' => __('Bookings'),
                'url' => 'bookings.php',
                'icon' => 'fa-regular fa-calendar-check',
                'is_active' => ($active_page === 'bookings.php')
            ],
            [
                'id' => 'reviews.php',
                'label' => __('Reviews'),
                'url' => 'reviews.php',
                'icon' => 'fa-regular fa-star',
                'is_active' => ($active_page === 'reviews.php')
            ],
            [
                'id' => 'categories.php',
                'label' => __('Categories'),
                'url' => 'categories.php',
                'icon' => 'fa-solid fa-tags',
                'is_active' => ($active_page === 'categories.php')
            ],
        ];
    } else {
        // Provider Menu Items
        $menu_items = [
            [
                'id' => 'dashboard.php',
                'label' => __('Dashboard'),
                'url' => 'dashboard.php',
                'icon' => 'fa-solid fa-chart-line',
                'is_active' => ($active_page === 'dashboard.php')
            ],
            [
                'id' => 'my-services.php',
                'label' => __('My Services'),
                'url' => 'my-services.php',
                'icon' => 'fa-solid fa-toolbox',
                'is_active' => in_array($active_page, ['my-services.php', 'add-service.php', 'edit-service.php'], true)
            ],
            [
                'id' => 'bookings.php',
                'label' => __('Bookings'),
                'url' => 'bookings.php',
                'icon' => 'fa-regular fa-calendar-check',
                'is_active' => ($active_page === 'bookings.php' || $active_page === 'booking-detail.php') && $current_status !== 'completed'
            ],
            [
                'id' => 'reviews.php',
                'label' => __('Reviews'),
                'url' => 'reviews.php',
                'icon' => 'fa-regular fa-star',
                'is_active' => ($active_page === 'reviews.php')
            ],
            [
                'id' => 'earnings',
                'label' => __('Earnings'),
                'url' => 'bookings.php?status=completed',
                'icon' => 'fa-solid fa-wallet',
                'is_active' => ($active_page === 'bookings.php' && $current_status === 'completed')
            ],
            [
                'id' => 'profile.php',
                'label' => __('Profile'),
                'url' => 'profile.php',
                'icon' => 'fa-regular fa-user',
                'is_active' => ($active_page === 'profile.php')
            ],
        ];
    }

    $nav_base_class = 'group flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-xl transition-all';
    $active_class = 'bg-brand-primary text-white shadow-xs font-semibold';
    $inactive_class = 'text-brand-text hover:bg-brand-surface hover:text-brand-primary';
    ?>
    <aside class="w-64 flex-shrink-0 hidden md:block bg-white/70 backdrop-blur-md border-l border-brand-border p-4 h-full min-h-[calc(100vh-4rem)]">
        <nav class="space-y-1.5 flex flex-col justify-between h-full">
            <div class="space-y-1.5">
                <?php foreach ($menu_items as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="<?php echo $nav_base_class; ?> <?php echo $item['is_active'] ? $active_class : $inactive_class; ?>">
                        <i class="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?> text-lg w-5 text-center <?php echo $item['is_active'] ? 'text-white' : 'text-brand-textMuted group-hover:text-brand-primary'; ?>"></i>
                        <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="pt-4 mt-6 border-t border-brand-border">
                <a class="group flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-xl text-brand-danger hover:bg-brand-dangerLight transition-all" 
                   href="/local-services-platform/public/logout.php">
                    <i class="fa-solid fa-arrow-right-from-bracket text-lg w-5 text-center"></i>
                    <span><?php echo __('Logout'); ?></span>
                </a>
            </div>
        </nav>
    </aside>
    <?php
}
