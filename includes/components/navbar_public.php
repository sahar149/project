<?php
/**
 * Dabberha (دبرها) - Public & Customer Top Navigation
 * Location: includes/components/navbar_public.php
 *
 * @param array $params ['active_page' => string]
 */

function renderPublicNavbar(array $params = []): void {
    $active = $params['active_page'] ?? '';
    $is_logged_in = isLoggedIn();
    $role = getUserRole();
    $name = getUserName();
    ?>
    <header class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-brand-border shadow-xs">
        <div class="flex justify-between items-center px-6 py-3.5 max-w-7xl mx-auto">
            <!-- Brand Logo -->
            <a href="/local-services-platform/index.php" class="text-2xl font-black tracking-tight text-brand-primary flex items-center gap-2">
                <i class="fa-solid fa-house-chimney text-xl"></i>
                <span><?php echo __('Dabberha'); ?></span>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="/local-services-platform/index.php" 
                   class="<?php echo $active === 'home' ? 'text-brand-primary border-b-2 border-brand-primary font-bold' : 'text-brand-text hover:text-brand-primary font-medium'; ?> pb-1 transition-colors">
                    <?php echo __('Home'); ?>
                </a>

                <a href="/local-services-platform/public/browse-services.php" 
                   class="<?php echo $active === 'services' ? 'text-brand-primary border-b-2 border-brand-primary font-bold' : 'text-brand-text hover:text-brand-primary font-medium'; ?> pb-1 transition-colors">
                    <?php echo __('Find Services'); ?>
                </a>

                <a href="/local-services-platform/index.php#how-it-works" 
                   class="text-brand-text hover:text-brand-primary font-medium pb-1 transition-colors">
                    <?php echo __('How it Works'); ?>
                </a>

                <a href="/local-services-platform/index.php#categories" 
                   class="text-brand-text hover:text-brand-primary font-medium pb-1 transition-colors">
                    <?php echo __('Categories'); ?>
                </a>
            </nav>

            <!-- Right Side (Auth & Account Links) -->
            <div class="flex items-center gap-3">
                <?php if ($is_logged_in): ?>
                    <!-- User greeting -->
                    <span class="hidden sm:flex items-center gap-2 text-sm font-semibold text-brand-text bg-brand-surface px-3 py-1.5 rounded-xl border border-brand-border">
                        <span class="material-symbols-outlined text-brand-primary text-[20px]">person</span>
                        <span><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>

                    <!-- Role-based Dashboard / Bookings link -->
                    <?php if ($role === 'customer'): ?>
                        <a href="/local-services-platform/public/my-bookings.php" 
                           class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-brand-primary bg-brand-primaryLight hover:bg-brand-primary/20 transition-all">
                            <span class="material-symbols-outlined text-[18px]">event_note</span>
                            <span><?php echo __('My Bookings'); ?></span>
                        </a>
                    <?php elseif ($role === 'provider'): ?>
                        <a href="/local-services-platform/provider/dashboard.php" 
                           class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-brand-primary bg-brand-primaryLight hover:bg-brand-primary/20 transition-all">
                            <span class="material-symbols-outlined text-[18px]">dashboard</span>
                            <span><?php echo __('Dashboard'); ?></span>
                        </a>
                    <?php elseif ($role === 'admin'): ?>
                        <a href="/local-services-platform/admin/dashboard.php" 
                           class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-brand-primary bg-brand-primaryLight hover:bg-brand-primary/20 transition-all">
                            <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                            <span><?php echo __('Admin Panel'); ?></span>
                        </a>
                    <?php endif; ?>

                    <a href="/local-services-platform/public/logout.php" 
                       class="bg-brand-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-primaryHover active:scale-95 transition-all shadow-xs">
                        <?php echo __('Logout'); ?>
                    </a>
                <?php else: ?>
                    <a href="/local-services-platform/public/login.php" 
                       class="text-brand-text hover:text-brand-primary font-semibold text-sm px-4 py-2 transition-colors">
                        <?php echo __('Login'); ?>
                    </a>

                    <a href="/local-services-platform/public/register.php" 
                       class="bg-brand-primary text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-brand-primaryHover active:scale-95 transition-all shadow-xs">
                        <?php echo __('Join Now'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php
}
