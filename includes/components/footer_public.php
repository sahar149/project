<?php
/**
 * Dabberha (دبرها) - Public Footer & Mobile Bottom Bar
 * Location: includes/components/footer_public.php
 *
 * @param array $params ['active_page' => string]
 */

function renderPublicFooter(array $params = []): void {
    $active = $params['active_page'] ?? '';
    $is_logged_in = isLoggedIn();
    $role = getUserRole();
    ?>
    <!-- Desktop Footer -->
    <footer class="bg-brand-900 text-white mt-auto pt-16 pb-12 border-t border-brand-800">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-10">
            <!-- Col 1: Brand Info -->
            <div class="space-y-4">
                <a href="/local-services-platform/index.php" class="text-2xl font-black text-brand-primaryLight tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-house-chimney text-xl text-brand-primary"></i>
                    <span><?php echo __('Dabberha'); ?></span>
                </a>
                <p class="text-brand-300 text-sm leading-relaxed">
                    <?php echo __('Your trusted local service marketplace connecting customers with verified professional service providers.'); ?>
                </p>
            </div>

            <!-- Col 2: Quick Links -->
            <div class="space-y-3">
                <h4 class="font-bold text-white text-base"><?php echo __('Quick Links'); ?></h4>
                <ul class="space-y-2 text-sm text-brand-300">
                    <li><a href="/local-services-platform/index.php" class="hover:text-white transition-colors"><?php echo __('Home'); ?></a></li>
                    <li><a href="/local-services-platform/public/browse-services.php" class="hover:text-white transition-colors"><?php echo __('Find Services'); ?></a></li>
                    <li><a href="/local-services-platform/index.php#how-it-works" class="hover:text-white transition-colors"><?php echo __('How it Works'); ?></a></li>
                    <li><a href="/local-services-platform/index.php#categories" class="hover:text-white transition-colors"><?php echo __('Categories'); ?></a></li>
                </ul>
            </div>

            <!-- Col 3: For Providers -->
            <div class="space-y-3">
                <h4 class="font-bold text-white text-base"><?php echo __('For Providers'); ?></h4>
                <ul class="space-y-2 text-sm text-brand-300">
                    <li><a href="/local-services-platform/public/register.php?role=provider" class="hover:text-white transition-colors"><?php echo __('Become a Provider'); ?></a></li>
                    <li><a href="/local-services-platform/public/login.php?role=provider" class="hover:text-white transition-colors"><?php echo __('Provider Dashboard'); ?></a></li>
                    <li><a href="/local-services-platform/public/login.php?role=admin" class="hover:text-white transition-colors"><?php echo __('Admin Portal'); ?></a></li>
                </ul>
            </div>

            <!-- Col 4: Trust & Support -->
            <div class="space-y-3">
                <h4 class="font-bold text-white text-base"><?php echo __('Trust & Support'); ?></h4>
                <ul class="space-y-2 text-sm text-brand-300">
                    <li><a href="#" class="hover:text-white transition-colors"><?php echo __('Terms of Service'); ?></a></li>
                    <li><a href="#" class="hover:text-white transition-colors"><?php echo __('Privacy Policy'); ?></a></li>
                    <li><a href="#" class="hover:text-white transition-colors"><?php echo __('Help Center'); ?></a></li>
                    <li><a href="#" class="hover:text-white transition-colors"><?php echo __('Contact Us'); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 mt-12 pt-8 border-t border-brand-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-brand-400">
            <p>&copy; 2026 - 2027 <?php echo __('Dabberha. All rights reserved.'); ?></p>
            <div class="flex items-center gap-6">
                <span><?php echo __('Made with excellence for our local community'); ?></span>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-4 pb-5 pt-3 bg-white/95 backdrop-blur-lg border-t border-brand-border shadow-lg z-50 rounded-t-2xl">
        <!-- Home -->
        <a href="/local-services-platform/index.php" 
           class="flex flex-col items-center justify-center <?php echo $active === 'home' ? 'text-brand-primary font-bold' : 'text-brand-textMuted'; ?> px-3 py-1">
            <span class="material-symbols-outlined text-[22px]">home</span>
            <span class="text-[11px]"><?php echo __('Home'); ?></span>
        </a>

        <!-- Explore -->
        <a href="/local-services-platform/public/browse-services.php" 
           class="flex flex-col items-center justify-center <?php echo $active === 'services' ? 'text-brand-primary font-bold' : 'text-brand-textMuted'; ?> px-3 py-1">
            <span class="material-symbols-outlined text-[22px]">search</span>
            <span class="text-[11px]"><?php echo __('Explore'); ?></span>
        </a>

        <!-- Bookings or Join -->
        <?php if ($is_logged_in && $role === 'customer'): ?>
            <a href="/local-services-platform/public/my-bookings.php" 
               class="flex flex-col items-center justify-center <?php echo $active === 'bookings' ? 'text-brand-primary font-bold' : 'text-brand-textMuted'; ?> px-3 py-1">
                <span class="material-symbols-outlined text-[22px]">event_note</span>
                <span class="text-[11px]"><?php echo __('Bookings'); ?></span>
            </a>
        <?php elseif ($is_logged_in && $role === 'provider'): ?>
            <a href="/local-services-platform/provider/dashboard.php" 
               class="flex flex-col items-center justify-center text-brand-textMuted px-3 py-1">
                <span class="material-symbols-outlined text-[22px]">dashboard</span>
                <span class="text-[11px]"><?php echo __('Dashboard'); ?></span>
            </a>
        <?php else: ?>
            <a href="/local-services-platform/public/register.php" 
               class="flex flex-col items-center justify-center text-brand-textMuted px-3 py-1">
                <span class="material-symbols-outlined text-[22px]">person_add</span>
                <span class="text-[11px]"><?php echo __('Join'); ?></span>
            </a>
        <?php endif; ?>

        <!-- Profile or Login -->
        <?php if ($is_logged_in): ?>
            <?php if ($role === 'provider'): ?>
                <a href="/local-services-platform/provider/profile.php" class="flex flex-col items-center justify-center text-brand-textMuted px-3 py-1">
                    <span class="material-symbols-outlined text-[22px]">person</span>
                    <span class="text-[11px]"><?php echo __('Profile'); ?></span>
                </a>
            <?php elseif ($role === 'admin'): ?>
                <a href="/local-services-platform/admin/dashboard.php" class="flex flex-col items-center justify-center text-brand-textMuted px-3 py-1">
                    <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
                    <span class="text-[11px]"><?php echo __('Admin'); ?></span>
                </a>
            <?php else: ?>
                <a href="/local-services-platform/public/my-bookings.php" class="flex flex-col items-center justify-center text-brand-textMuted px-3 py-1">
                    <span class="material-symbols-outlined text-[22px]">person</span>
                    <span class="text-[11px]"><?php echo __('Profile'); ?></span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="/local-services-platform/public/login.php" class="flex flex-col items-center justify-center text-brand-textMuted px-3 py-1">
                <span class="material-symbols-outlined text-[22px]">login</span>
                <span class="text-[11px]"><?php echo __('Login'); ?></span>
            </a>
        <?php endif; ?>
    </nav>
    </body>
    </html>
    <?php
}
