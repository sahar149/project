<?php
/**
 * Dabberha (دبرها) - Edit Service (Provider Panel)
 * Location: provider/edit-service.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/categories_db.php';
require_once __DIR__ . '/../includes/db/services_db.php';

requireRole('provider');

$provider_id = getUserId();
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id <= 0) {
    header('Location: my-services.php');
    exit;
}

$service = getServiceById($service_id);

if (!$service || (int)$service['provider_id'] !== $provider_id) {
    header('Location: my-services.php');
    exit;
}

$categories = getAllCategories();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $price_type = $_POST['price_type'] ?? 'fixed';

    if (empty($title) || empty($description) || $price <= 0 || $category_id <= 0) {
        $error = 'Please fill all fields correctly';
    } else {
        $updated = updateService($service_id, $provider_id, [
            'category_id' => $category_id,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'price_type' => $price_type
        ]);

        if ($updated) {
            $success = 'Service updated successfully!';
            $service['category_id'] = $category_id;
            $service['title'] = $title;
            $service['description'] = $description;
            $service['price'] = $price;
            $service['price_type'] = $price_type;
        } else {
            $error = 'Failed to update service. Please try again.';
        }
    }
}

renderHead(['title' => __('Edit Service') . ' - ' . __('Dabberha')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-4xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-solid fa-pen-to-square text-brand-primary"></i>
                        <span><?php echo __('Edit Service'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('Update your service listing details and pricing'); ?></p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <?php echo renderAlert($error, 'danger'); ?>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <?php echo renderAlert($success, 'success'); ?>
            <?php endif; ?>

            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-brand-border">
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-brand-text mb-2" for="category_id">
                            <?php echo __('Category'); ?> <span class="text-brand-danger">*</span>
                        </label>
                        <select name="category_id" id="category_id" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required>
                            <option value=""><?php echo __('Select Category'); ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (int)$cat['id'] === (int)$service['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-brand-text mb-2" for="title">
                            <?php echo __('Service Title'); ?> <span class="text-brand-danger">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($service['title']); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-brand-text mb-2" for="description">
                            <?php echo __('Service Description'); ?> <span class="text-brand-danger">*</span>
                        </label>
                        <textarea name="description" id="description" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="price">
                                <?php echo __('Price (د.ل)'); ?> <span class="text-brand-danger">*</span>
                            </label>
                            <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($service['price']); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="price_type">
                                <?php echo __('Price Type'); ?> <span class="text-brand-danger">*</span>
                            </label>
                            <select name="price_type" id="price_type" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required>
                                <option value="fixed" <?php echo $service['price_type'] === 'fixed' ? 'selected' : ''; ?>><?php echo __('Fixed Price'); ?></option>
                                <option value="hourly" <?php echo $service['price_type'] === 'hourly' ? 'selected' : ''; ?>><?php echo __('Hourly Rate'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-3">
                        <a href="my-services.php" class="px-5 py-2.5 rounded-xl border border-brand-border bg-brand-surface hover:bg-brand-surfaceAlt text-brand-text text-sm font-bold transition-all">
                            <?php echo __('Cancel'); ?>
                        </a>
                        <button type="submit" class="bg-brand-primary hover:bg-brand-primaryHover text-white px-6 py-2.5 rounded-xl text-sm font-bold transition-all shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span><?php echo __('Update Service'); ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>