<?php
/**
 * Dabberha (دبرها) - Manage Users (Admin Panel)
 * Location: admin/users.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/sidebar.php';
require_once __DIR__ . '/../includes/components/header_dashboard.php';

require_once __DIR__ . '/../includes/db/users_db.php';

requireRole('admin');

$message = '';
$message_type = '';

// Toggle user status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $user = getUserById($user_id);
    
    if ($user) {
        $new_status = ($user['status'] === 'active') ? 'inactive' : 'active';
        if (updateUserStatus($user_id, $new_status)) {
            $message = __('User status updated successfully!');
            $message_type = 'success';
        }
    }
}

// Delete user (non-admin only)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $user = getUserById($user_id);
    if ($user && $user['role'] !== 'admin') {
        if (deleteUser($user_id)) {
            $message = __('User deleted successfully!');
            $message_type = 'success';
        }
    }
}

$users = getAllUsers();

renderHead(['title' => __('Manage Users') . ' - ' . __('Admin Panel')]);
?>

<div class="min-h-screen flex flex-col">
    <?php renderDashboardHeader([
        'title' => __('Manage Users'),
        'subtitle' => __('View and manage all registered users'),
        'role' => 'admin'
    ]); ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php renderSidebar('admin', 'users.php'); ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-solid fa-users text-brand-primary"></i>
                        <span><?php echo __('Manage Users'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('View and manage all registered users'); ?></p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo renderAlert($message, $message_type); ?>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-brand-border bg-brand-surface/60 text-brand-text font-bold text-xs">
                                <th class="py-4 px-6"><?php echo __('ID'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Name'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Email'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Role'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Phone'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Status'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Joined'); ?></th>
                                <th class="py-4 px-6 text-center"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-sm">
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-brand-surface/40 transition-colors">
                                    <td class="py-4 px-6 text-xs text-brand-textMuted font-mono">#<?php echo (int) $user['id']; ?></td>
                                    <td class="py-4 px-6 font-bold text-brand-900"><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td class="py-4 px-6 text-brand-textMuted"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo $user['role'] === 'admin' ? 'bg-red-50 text-red-700 border border-red-200' : ($user['role'] === 'provider' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700 border border-gray-200'); ?>">
                                            <?php echo htmlspecialchars(__($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-brand-textMuted"><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                    <td class="py-4 px-6"><?php echo renderStatusBadge($user['status']); ?></td>
                                    <td class="py-4 px-6 text-brand-textMuted text-xs"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <div class="flex justify-center gap-2">
                                                <a href="users.php?toggle_status=1&id=<?php echo (int) $user['id']; ?>" 
                                                   class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 flex items-center justify-center transition-colors shadow-2xs" 
                                                   title="<?php echo __('Change status'); ?>" 
                                                   onclick="return confirm('<?php echo htmlspecialchars(__('Change status?'), ENT_QUOTES); ?>')">
                                                    <i class="fa-solid <?php echo $user['status'] === 'active' ? 'fa-pause' : 'fa-play'; ?> text-xs"></i>
                                                </a>
                                                <a href="users.php?delete=1&id=<?php echo (int) $user['id']; ?>" 
                                                   class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 flex items-center justify-center transition-colors shadow-2xs" 
                                                   title="<?php echo __('Delete user'); ?>" 
                                                   onclick="return confirm('<?php echo htmlspecialchars(__('Delete this user?'), ENT_QUOTES); ?>')">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-brand-textLight font-bold bg-brand-surface px-2.5 py-1 rounded-md border border-brand-border">
                                                <i class="fa-solid fa-lock text-[10px] ml-1"></i><?php echo __('Protected'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>