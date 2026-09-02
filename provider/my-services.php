<?php
/**
 * Dabberha (دبرها) - My Services (Provider Panel)
 * Location: provider/my-services.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/services_db.php';

requireRole('provider');

$provider_id = getUserId();
$message = '';
$message_type = '';

// Handle service deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $service_id = (int)$_GET['id'];
    if (deleteService($service_id, $provider_id)) {
        $message = 'Service deleted successfully!';
        $message_type = 'success';
    }
}

$services = getServicesByProvider($provider_id);

renderHead(['title' => __('My Services - Provider Dashboard')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-solid fa-briefcase text-brand-primary"></i>
                        <span><?php echo __('My Services'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('Manage your active service listings and pricing'); ?></p>
                </div>
                <a href="add-service.php" class="inline-flex items-center gap-2 bg-brand-primary hover:bg-brand-primaryHover text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                    <span><?php echo __('Add New Service'); ?></span>
                </a>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo renderAlert($message, $message_type); ?>
            <?php endif; ?>

            <?php if (count($services) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($services as $service): ?>
                        <article class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex flex-col justify-between hover:border-brand-primary/40 hover:shadow-md transition-all group">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <span class="inline-flex items-center gap-1.5 bg-brand-surface px-3 py-1 rounded-full border border-brand-border text-xs font-bold text-brand-primary">
                                        <i class="<?php echo htmlspecialchars(getCategoryFAIcon($service['category_name'] ?? '', $service['category_icon'] ?? '')); ?> text-xs"></i>
                                        <span><?php echo htmlspecialchars($service['category_name'] ?? __('Uncategorized')); ?></span>
                                    </span>
                                    <?php echo renderStatusBadge($service['price_type'] ?? 'fixed'); ?>
                                </div>

                                <h2 class="text-lg font-bold text-brand-900 mb-2 group-hover:text-brand-primary transition-colors">
                                    <?php echo htmlspecialchars($service['title']); ?>
                                </h2>

                                <p class="text-brand-textMuted text-xs line-clamp-3 mb-5 leading-relaxed">
                                    <?php echo htmlspecialchars($service['description'] ?? ''); ?>
                                </p>
                            </div>

                            <div>
                                <div class="flex items-baseline gap-1 mb-5">
                                    <span class="text-2xl font-black text-brand-primary" dir="rtl">
                                        <?php echo number_format($service['price'], 2); ?> د.ل
                                    </span>
                                    <span class="text-xs text-brand-textMuted font-medium">
                                        / <?php echo htmlspecialchars(__($service['price_type'])); ?>
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 border-t border-brand-border pt-4">
                                    <a href="edit-service.php?id=<?php echo (int)$service['id']; ?>" 
                                       class="flex-1 flex items-center justify-center gap-2 bg-brand-surface hover:bg-brand-surfaceAlt text-brand-text border border-brand-border py-2 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span><?php echo __('Edit'); ?></span>
                                    </a>
                                    <a href="my-services.php?delete=1&id=<?php echo (int)$service['id']; ?>" 
                                       onclick="return confirm('<?php echo htmlspecialchars(__('Are you sure you want to delete this service?'), ENT_QUOTES); ?>');" 
                                       class="w-10 h-8 flex items-center justify-center rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition-colors shadow-2xs"
                                       title="<?php echo __('Delete'); ?>">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl p-12 shadow-soft border border-brand-border text-center">
                    <?php echo renderEmptyState(
                        'fa-solid fa-briefcase',
                        'No Services Added Yet',
                        'Start by adding your first service to get bookings from customers.',
                        'add-service.php',
                        'Add Your First Service'
                    ); ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>