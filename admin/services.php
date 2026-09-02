<?php
/**
 * Dabberha (دبرها) - Manage Services (Admin Panel)
 * Location: admin/services.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/sidebar.php';
require_once __DIR__ . '/../includes/components/header_dashboard.php';

require_once __DIR__ . '/../includes/db/services_db.php';

requireRole('admin');

$message = '';
$message_type = '';

// Delete service
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $service_id = (int)$_GET['id'];
    if (deleteService($service_id)) {
        $message = 'Service deleted successfully!';
        $message_type = 'success';
    }
}

$services = getAllServices();

renderHead(['title' => __('Manage Services') . ' - ' . __('Admin Panel')]);
?>

<div class="min-h-screen flex flex-col">
    <?php renderDashboardHeader([
        'title' => __('Manage Services'),
        'subtitle' => __('View and manage all listed services'),
        'role' => 'admin'
    ]); ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php renderSidebar('admin', 'services.php'); ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-solid fa-briefcase text-brand-primary"></i>
                        <span><?php echo __('Manage Services'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('View and manage all services across the platform'); ?></p>
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
                                <th class="py-4 px-6">ID</th>
                                <th class="py-4 px-6"><?php echo __('Title'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Category'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Provider'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Price'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Type'); ?></th>
                                <th class="py-4 px-6 text-center"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-sm">
                            <?php if (count($services) > 0): ?>
                                <?php foreach ($services as $service): ?>
                                    <tr class="hover:bg-brand-surface/40 transition-colors">
                                        <td class="py-4 px-6 text-xs text-brand-textMuted font-mono">#<?php echo (int) $service['id']; ?></td>
                                        <td class="py-4 px-6 font-bold text-brand-900"><?php echo htmlspecialchars($service['title']); ?></td>
                                        <td class="py-4 px-6 text-brand-textMuted">
                                            <span class="inline-flex items-center gap-1.5 bg-brand-surface px-2.5 py-1 rounded-lg border border-brand-border text-xs font-semibold">
                                                <i class="<?php echo htmlspecialchars(getCategoryFAIcon($service['category_name'] ?? '', $service['category_icon'] ?? '')); ?> text-brand-primary"></i>
                                                <?php echo htmlspecialchars($service['category_name'] ?? __('Uncategorized')); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-brand-textMuted font-medium"><?php echo htmlspecialchars($service['provider_name']); ?></td>
                                        <td class="py-4 px-6 font-black text-brand-primary" dir="rtl"><?php echo number_format($service['price'], 2); ?> د.ل</td>
                                        <td class="py-4 px-6"><?php echo renderStatusBadge($service['price_type'] ?? 'fixed'); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="services.php?delete=1&id=<?php echo (int) $service['id']; ?>" 
                                               class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors shadow-2xs" 
                                               title="<?php echo __('Delete Service'); ?>" 
                                               onclick="return confirm('<?php echo htmlspecialchars(__('Delete this service?'), ENT_QUOTES); ?>')">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 px-6 text-center">
                                        <?php echo renderEmptyState('fa-solid fa-briefcase', 'No services found.'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>