<?php
/**
 * Dabberha (دبرها) - Public & Customer Top Navigation
 * Location: includes/components/navbar_public.php
 *
 * @param array $params ['active_page' => string]
 */

require_once __DIR__ . '/../notifications.php';

function renderPublicNavbar(array $params = []): void {
    $active = $params['active_page'] ?? '';
    $is_logged_in = isLoggedIn();
    $role = getUserRole();
    $name = getUserName();
    $user_id = $is_logged_in ? getUserId() : 0;
    
    $unread_count = $is_logged_in ? getUnreadCount($user_id) : 0;
    $notifications = $is_logged_in ? getNotifications($user_id, 8) : [];
    ?>
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-brand-border shadow-xs">
        <div class="flex justify-between items-center px-4 sm:px-6 py-3 max-w-7xl mx-auto">
            <!-- Brand Logo -->
            <a href="/local-services-platform/index.php" class="text-2xl font-black tracking-tight text-brand-primary flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-house-chimney text-xl"></i>
                <span><?php echo __('Dabberha'); ?></span>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-7">
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
            <div class="flex items-center gap-2.5 sm:gap-3">
                <?php if ($is_logged_in): ?>
                    <!-- جرس الإشعارات التفاعلي للعميل والمستخدمين -->
                    <div class="relative">
                        <button type="button" 
                                id="navNotifBtn"
                                class="w-10 h-10 rounded-xl bg-brand-surface hover:bg-brand-primaryLight/50 border border-brand-border flex items-center justify-center text-brand-text hover:text-brand-primary relative transition-all shadow-2xs"
                                aria-label="الإشعارات"
                                title="الإشعارات">
                            <i class="fa-regular fa-bell text-lg"></i>
                            <span id="navNotifBadge" class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-brand-danger text-[10px] font-bold text-white ring-2 ring-white <?php echo $unread_count > 0 ? '' : 'hidden'; ?>">
                                <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                            </span>
                        </button>

                        <!-- القائمة المنسدلة للإشعارات -->
                        <div id="navNotifDropdown" class="hidden absolute left-0 sm:left-auto right-auto sm:right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-brand-border z-50 overflow-hidden transform transition-all duration-200 origin-top">
                            <!-- رأس القائمة -->
                            <div class="px-4 py-3 bg-brand-surface/80 border-b border-brand-border flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-bell text-brand-primary text-sm"></i>
                                    <span class="font-bold text-sm text-brand-900">الإشعارات والتنبيهات</span>
                                    <?php if ($unread_count > 0): ?>
                                        <span id="navNotifDropdownCount" class="bg-brand-primary text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $unread_count; ?> جديد</span>
                                    <?php endif; ?>
                                </div>
                                <button type="button" onclick="markAllNotificationsRead()" class="text-[11px] font-bold text-brand-primary hover:underline">
                                    تحديد الكل كمقروء
                                </button>
                            </div>

                            <!-- قائمة الإشعارات -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-brand-border/60" id="navNotifList">
                                <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <?php 
                                        $details = getNotificationDetails($notif); 
                                        $open_url = '/local-services-platform/public/notifications-action.php?action=open&id=' . (int)$details['id'] . '&redirect=' . urlencode($details['action_url']);
                                        ?>
                                        <div id="nav-notif-<?php echo $details['id']; ?>" class="p-3.5 hover:bg-brand-surface/50 transition-colors flex items-start gap-3 <?php echo !$details['is_read'] ? 'bg-brand-primaryLight/15' : ''; ?>">
                                            <div class="w-8 h-8 rounded-full <?php echo $details['icon_bg']; ?> <?php echo $details['icon_color']; ?> flex items-center justify-center shrink-0 text-sm mt-0.5 shadow-2xs">
                                                <i class="<?php echo $details['icon']; ?>"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-brand-text font-medium leading-relaxed">
                                                    <?php echo htmlspecialchars($details['message']); ?>
                                                </p>
                                                
                                                <div class="flex items-center justify-between mt-2 pt-1 border-t border-brand-border/40 gap-2">
                                                    <span class="text-[10px] text-brand-textMuted flex items-center gap-1 font-mono">
                                                        <i class="fa-regular fa-clock text-[9px]"></i>
                                                        <span><?php echo $details['time_ago']; ?></span>
                                                    </span>

                                                    <a href="<?php echo htmlspecialchars($open_url); ?>" 
                                                       onclick="handleNotifClick(event, <?php echo $details['id']; ?>, '<?php echo htmlspecialchars($details['action_url'], ENT_QUOTES); ?>', <?php echo $details['is_read'] ? 'false' : 'true'; ?>)"
                                                       class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-primary hover:text-brand-primaryHover transition-colors bg-white px-2 py-0.5 rounded-md border border-brand-border shadow-2xs">
                                                        <span><?php echo $details['action_label']; ?></span>
                                                        <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php if (!$details['is_read']): ?>
                                                <span class="notif-dot w-2 h-2 rounded-full bg-brand-primary shrink-0 mt-1.5 ring-2 ring-brand-primaryLight"></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="py-8 px-4 text-center">
                                        <div class="w-12 h-12 rounded-full bg-brand-surface text-brand-textMuted flex items-center justify-center mx-auto mb-2 text-xl">
                                            <i class="fa-regular fa-bell-slash"></i>
                                        </div>
                                        <p class="text-xs font-bold text-brand-text">لا توجد إشعارات جديدة</p>
                                        <p class="text-[11px] text-brand-textMuted mt-0.5">ستصلك تنبيهات فورية عند قبول، إتمام، أو تحديث حجوزاتك</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- أسفل القائمة -->
                            <div class="p-2.5 bg-brand-surface/40 border-t border-brand-border text-center">
                                <a href="/local-services-platform/public/my-bookings.php" class="text-xs font-bold text-brand-primary hover:underline inline-flex items-center gap-1">
                                    <i class="fa-regular fa-calendar-check text-[11px]"></i>
                                    <span>عرض وإدارة جميع الحجوزات</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- اسم المستخدم -->
                    <span class="hidden sm:flex items-center gap-2 text-sm font-semibold text-brand-text bg-brand-surface px-3 py-1.5 rounded-xl border border-brand-border">
                        <span class="material-symbols-outlined text-brand-primary text-[20px]">person</span>
                        <span><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>

                    <!-- Role-based Dashboard / Bookings link -->
                    <?php if ($role === 'customer'): ?>
                        <a href="/local-services-platform/public/my-bookings.php" 
                           class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-brand-primary bg-brand-primaryLight hover:bg-brand-primary/20 transition-all">
                            <span class="material-symbols-outlined text-[17px]">event_note</span>
                            <span><?php echo __('My Bookings'); ?></span>
                        </a>
                    <?php elseif ($role === 'provider'): ?>
                        <a href="/local-services-platform/provider/dashboard.php" 
                           class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-brand-primary bg-brand-primaryLight hover:bg-brand-primary/20 transition-all">
                            <span class="material-symbols-outlined text-[17px]">dashboard</span>
                            <span><?php echo __('Dashboard'); ?></span>
                        </a>
                    <?php elseif ($role === 'admin'): ?>
                        <a href="/local-services-platform/admin/dashboard.php" 
                           class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-brand-primary bg-brand-primaryLight hover:bg-brand-primary/20 transition-all">
                            <span class="material-symbols-outlined text-[17px]">admin_panel_settings</span>
                            <span><?php echo __('Admin Panel'); ?></span>
                        </a>
                    <?php endif; ?>

                    <a href="/local-services-platform/public/logout.php" 
                       class="bg-brand-primary text-white px-3.5 py-2 rounded-xl text-xs font-semibold hover:bg-brand-primaryHover active:scale-95 transition-all shadow-xs">
                        <?php echo __('Logout'); ?>
                    </a>
                <?php else: ?>
                    <a href="/local-services-platform/public/login.php" 
                       class="text-brand-text hover:text-brand-primary font-semibold text-sm px-3.5 py-2 transition-colors">
                        <?php echo __('Login'); ?>
                    </a>

                    <a href="/local-services-platform/public/register.php" 
                       class="bg-brand-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-primaryHover active:scale-95 transition-all shadow-xs">
                        <?php echo __('Join Now'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($is_logged_in): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifBtn = document.getElementById('navNotifBtn');
        const notifDropdown = document.getElementById('navNotifDropdown');

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notifDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                    notifDropdown.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    notifDropdown.classList.add('hidden');
                }
            });
        }
    });

    function handleNotifClick(e, id, destUrl, isUnread) {
        if (!isUnread) return; // already read, proceed normally
        
        // Optimistic UI update
        const item = document.getElementById('nav-notif-' + id);
        if (item) {
            item.classList.remove('bg-brand-primaryLight/15');
            const dot = item.querySelector('.notif-dot');
            if (dot) dot.remove();
        }

        const badge = document.getElementById('navNotifBadge');
        if (badge) {
            let count = parseInt(badge.textContent.trim()) || 0;
            count = Math.max(0, count - 1);
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
            } else {
                badge.classList.add('hidden');
            }
        }

        const countBadge = document.getElementById('navNotifDropdownCount');
        if (countBadge) {
            let count = parseInt(countBadge.textContent.trim()) || 0;
            count = Math.max(0, count - 1);
            if (count > 0) {
                countBadge.textContent = count + ' جديد';
            } else {
                countBadge.classList.add('hidden');
            }
        }

        // Fire and proceed
        navigator.sendBeacon && navigator.sendBeacon('/local-services-platform/public/notifications-action.php?action=mark_read&id=' + id + '&ajax=1');
    }

    function markAllNotificationsRead() {
        fetch('/local-services-platform/public/notifications-action.php?action=mark_all_read&ajax=1', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('navNotifBadge');
                if (badge) badge.classList.add('hidden');
                
                const countBadge = document.getElementById('navNotifDropdownCount');
                if (countBadge) countBadge.classList.add('hidden');

                document.querySelectorAll('#navNotifList .notif-dot').forEach(el => el.remove());
                document.querySelectorAll('#navNotifList > div').forEach(el => {
                    el.classList.remove('bg-brand-primaryLight/15');
                });
            }
        })
        .catch(err => console.error('Error marking notifications as read:', err));
    }
    </script>
    <?php endif; ?>
    <?php
}
