<?php
/**
 * Dabberha (دبرها) - Dashboard Top Header Component
 * Location: includes/components/header_dashboard.php
 *
 * @param array $params [
 *     'title' => string,
 *     'subtitle' => string,
 *     'role' => 'admin' | 'provider',
 *     'unread_count' => int,
 *     'user_name' => string
 * ]
 */

function renderDashboardHeader(array $params = []): void {
    $title = $params['title'] ?? __('Dashboard');
    $subtitle = $params['subtitle'] ?? '';
    $role = $params['role'] ?? getUserRole();
    $unread_count = (int)($params['unread_count'] ?? 0);
    $user_name = $params['user_name'] ?? getUserName();
    $notif_url = ($role === 'provider') ? 'dashboard.php#notifications' : 'dashboard.php';
    ?>
    <header class="bg-white/80 backdrop-blur-md border-b border-brand-border sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand Logo & Section Title -->
                <div class="flex items-center gap-6">
                    <a class="flex items-center gap-2 text-2xl font-black text-brand-primary tracking-tight" href="/local-services-platform/index.php">
                        <i class="fa-solid fa-house-chimney text-xl"></i>
                        <span><?php echo __('Dabberha'); ?></span>
                    </a>
                    
                    <div class="hidden sm:block h-6 w-px bg-brand-border"></div>

                    <div class="hidden sm:flex flex-col">
                        <h1 class="text-base font-bold text-brand-900 leading-tight"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <?php if (!empty($subtitle)): ?>
                            <span class="text-xs text-brand-textMuted"><?php echo htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Side: Notifications & User Profile -->
                <div class="flex items-center gap-4">
                    <?php if ($role === 'provider'): ?>
                        <a href="<?php echo htmlspecialchars($notif_url, ENT_QUOTES, 'UTF-8'); ?>" 
                           id="header-notif-btn"
                           class="w-10 h-10 rounded-xl bg-brand-surface hover:bg-brand-surfaceAlt border border-brand-border flex items-center justify-center text-brand-textMuted hover:text-brand-primary relative transition-all" 
                           aria-label="<?php echo __('View notifications'); ?>"
                           title="<?php echo __('View notifications'); ?>">
                            <i class="fa-regular fa-bell text-lg"></i>
                            <span id="header-notif-badge" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-danger text-[9px] font-bold text-white ring-2 ring-white <?php echo $unread_count > 0 ? '' : 'hidden'; ?>">
                                <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <div class="flex items-center gap-3 bg-brand-surface/70 border border-brand-border px-3 py-1.5 rounded-xl">
                        <div class="h-8 w-8 rounded-lg bg-brand-primaryLight text-brand-primary flex items-center justify-center font-bold text-sm">
                            <i class="<?php echo $role === 'admin' ? 'fa-solid fa-shield-halved' : 'fa-regular fa-user'; ?>"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs text-brand-text leading-tight"><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="text-[10px] text-brand-textMuted leading-tight font-medium"><?php echo $role === 'admin' ? __('Administrator') : __('Service Provider'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <?php
}
