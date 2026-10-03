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

require_once __DIR__ . '/../notifications.php';

function renderDashboardHeader(array $params = []): void {
    $title = $params['title'] ?? __('Dashboard');
    $subtitle = $params['subtitle'] ?? '';
    $role = $params['role'] ?? getUserRole();
    $user_id = getUserId();
    $unread_count = (int)($params['unread_count'] ?? ($user_id ? getUnreadCount($user_id) : 0));
    $user_name = $params['user_name'] ?? getUserName();
    $notifications = $user_id ? getNotifications($user_id, 8) : [];
    ?>
    <header class="bg-white/95 backdrop-blur-md border-b border-brand-border sticky top-0 z-40">
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
                <div class="flex items-center gap-3">
                    <?php if ($role === 'provider'): ?>
                        <div class="relative">
                            <button type="button" 
                                    id="dashNotifBtn"
                                    class="w-10 h-10 rounded-xl bg-brand-surface hover:bg-brand-primaryLight/50 border border-brand-border flex items-center justify-center text-brand-text hover:text-brand-primary relative transition-all shadow-2xs" 
                                    aria-label="الإشعارات"
                                    title="الإشعارات">
                                <i class="fa-regular fa-bell text-lg"></i>
                                <span id="dashNotifBadge" class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-brand-danger text-[10px] font-bold text-white ring-2 ring-white <?php echo $unread_count > 0 ? '' : 'hidden'; ?>">
                                    <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                                </span>
                            </button>

                            <!-- القائمة المنسدلة للإشعارات للمزود -->
                            <div id="dashNotifDropdown" class="hidden absolute left-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-brand-border z-50 overflow-hidden transform transition-all duration-200 origin-top">
                                <div class="px-4 py-3 bg-brand-surface/80 border-b border-brand-border flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-bell text-brand-primary text-sm"></i>
                                        <span class="font-bold text-sm text-brand-900">إشعارات الحجوزات</span>
                                        <?php if ($unread_count > 0): ?>
                                            <span id="dashNotifDropdownCount" class="bg-brand-primary text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $unread_count; ?> جديد</span>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" onclick="markAllDashNotificationsRead()" class="text-[11px] font-bold text-brand-primary hover:underline">
                                        تحديد الكل كمقروء
                                    </button>
                                </div>

                                <div class="max-h-80 overflow-y-auto divide-y divide-brand-border/60" id="dashNotifList">
                                    <?php if (!empty($notifications)): ?>
                                        <?php foreach ($notifications as $notif): ?>
                                            <?php 
                                            $details = getNotificationDetails($notif); 
                                            $open_url = '/local-services-platform/public/notifications-action.php?action=open&id=' . (int)$details['id'] . '&redirect=' . urlencode($details['action_url']);
                                            ?>
                                            <div id="dash-notif-<?php echo $details['id']; ?>" class="p-3.5 hover:bg-brand-surface/50 transition-colors flex items-start gap-3 <?php echo !$details['is_read'] ? 'bg-brand-primaryLight/15' : ''; ?>">
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
                                                           onclick="handleDashNotifClick(event, <?php echo $details['id']; ?>, '<?php echo htmlspecialchars($details['action_url'], ENT_QUOTES); ?>', <?php echo $details['is_read'] ? 'false' : 'true'; ?>)"
                                                           class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-primary hover:text-brand-primaryHover transition-colors bg-white px-2 py-0.5 rounded-md border border-brand-border shadow-2xs">
                                                            <span><?php echo $details['action_label']; ?></span>
                                                            <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php if (!$details['is_read']): ?>
                                                    <span class="dash-notif-dot w-2 h-2 rounded-full bg-brand-primary shrink-0 mt-1.5 ring-2 ring-brand-primaryLight"></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="py-8 px-4 text-center">
                                            <div class="w-12 h-12 rounded-full bg-brand-surface text-brand-textMuted flex items-center justify-center mx-auto mb-2 text-xl">
                                                <i class="fa-regular fa-bell-slash"></i>
                                            </div>
                                            <p class="text-xs font-bold text-brand-text">لا توجد إشعارات جديدة</p>
                                            <p class="text-[11px] text-brand-textMuted mt-0.5">ستصلك تنبيهات فورية عند وصول طلبات حجز جديدة لخدماتك</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="p-2.5 bg-brand-surface/40 border-t border-brand-border text-center">
                                    <a href="/local-services-platform/provider/bookings.php" class="text-xs font-bold text-brand-primary hover:underline inline-flex items-center gap-1">
                                        <i class="fa-regular fa-calendar-check text-[11px]"></i>
                                        <span>عرض كافة طلبات الحجز</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const btn = document.getElementById('dashNotifBtn');
                            const dropdown = document.getElementById('dashNotifDropdown');

                            if (btn && dropdown) {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    dropdown.classList.toggle('hidden');
                                });

                                document.addEventListener('click', function(e) {
                                    if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                                        dropdown.classList.add('hidden');
                                    }
                                });

                                document.addEventListener('keydown', function(e) {
                                    if (e.key === 'Escape') {
                                        dropdown.classList.add('hidden');
                                    }
                                });
                            }
                        });

                        function handleDashNotifClick(e, id, destUrl, isUnread) {
                            if (!isUnread) return;
                            
                            const item = document.getElementById('dash-notif-' + id);
                            if (item) {
                                item.classList.remove('bg-brand-primaryLight/15');
                                const dot = item.querySelector('.dash-notif-dot');
                                if (dot) dot.remove();
                            }

                            const badge = document.getElementById('dashNotifBadge');
                            if (badge) {
                                let count = parseInt(badge.textContent.trim()) || 0;
                                count = Math.max(0, count - 1);
                                if (count > 0) {
                                    badge.textContent = count > 9 ? '9+' : count;
                                } else {
                                    badge.classList.add('hidden');
                                }
                            }

                            const countBadge = document.getElementById('dashNotifDropdownCount');
                            if (countBadge) {
                                let count = parseInt(countBadge.textContent.trim()) || 0;
                                count = Math.max(0, count - 1);
                                if (count > 0) {
                                    countBadge.textContent = count + ' جديد';
                                } else {
                                    countBadge.classList.add('hidden');
                                }
                            }

                            navigator.sendBeacon && navigator.sendBeacon('/local-services-platform/public/notifications-action.php?action=mark_read&id=' + id + '&ajax=1');
                        }

                        function markAllDashNotificationsRead() {
                            fetch('/local-services-platform/public/notifications-action.php?action=mark_all_read&ajax=1', {
                                method: 'POST'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const badge = document.getElementById('dashNotifBadge');
                                    if (badge) badge.classList.add('hidden');
                                    
                                    const countBadge = document.getElementById('dashNotifDropdownCount');
                                    if (countBadge) countBadge.classList.add('hidden');

                                    document.querySelectorAll('#dashNotifList .dash-notif-dot').forEach(el => el.remove());
                                    document.querySelectorAll('#dashNotifList > div').forEach(el => {
                                        el.classList.remove('bg-brand-primaryLight/15');
                                    });
                                }
                            })
                            .catch(err => console.error('Error marking notifications as read:', err));
                        }
                        </script>
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
