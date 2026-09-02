<?php
/**
 * Dabberha (دبرها) - Manage Categories (Admin Panel)
 * Location: admin/categories.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/sidebar.php';
require_once __DIR__ . '/../includes/components/header_dashboard.php';

require_once __DIR__ . '/../includes/db/categories_db.php';

requireRole('admin');

$message = '';
$message_type = '';

$available_icons = [
    ['icon' => 'fa-solid fa-wrench', 'label' => __('Maintenance & Tools')],
    ['icon' => 'fa-solid fa-faucet-drip', 'label' => __('Plumbing')],
    ['icon' => 'fa-solid fa-bolt', 'label' => __('Electrical')],
    ['icon' => 'fa-solid fa-broom', 'label' => __('Cleaning')],
    ['icon' => 'fa-solid fa-paint-roller', 'label' => __('Painting')],
    ['icon' => 'fa-solid fa-truck', 'label' => __('Moving & Delivery')],
    ['icon' => 'fa-solid fa-car-side', 'label' => __('Automotive')],
    ['icon' => 'fa-solid fa-graduation-cap', 'label' => __('Education & Tutoring')],
    ['icon' => 'fa-solid fa-snowflake', 'label' => __('AC & Cooling')],
    ['icon' => 'fa-solid fa-hammer', 'label' => __('Carpentry & Building')],
    ['icon' => 'fa-solid fa-leaf', 'label' => __('Gardening & Landscaping')],
    ['icon' => 'fa-solid fa-laptop', 'label' => __('Technology & IT')],
];

// Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    
    if (empty($icon)) {
        $icon = getCategoryFAIcon($name);
    }
    
    if (!empty($name)) {
        if (createCategory($name, $icon)) {
            $message = 'Category added successfully!';
            $message_type = 'success';
        }
    }
}

// Delete Category
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    if (deleteCategory($category_id)) {
        $message = 'Category deleted successfully!';
        $message_type = 'success';
    }
}

$categories = getAllCategories();

renderHead(['title' => __('Manage Categories') . ' - ' . __('Admin Panel')]);
?>

<div class="min-h-screen flex flex-col">
    <?php renderDashboardHeader([
        'title' => __('Manage Categories'),
        'subtitle' => __('Add and manage service categories'),
        'role' => 'admin'
    ]); ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php renderSidebar('admin', 'categories.php'); ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-solid fa-tags text-brand-primary"></i>
                        <span><?php echo __('Manage Categories'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('Add and organize marketplace service categories'); ?></p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo renderAlert($message, $message_type); ?>
            <?php endif; ?>

            <div class="flex flex-col xl:flex-row gap-8">
                <!-- Add Category Form -->
                <section class="w-full xl:w-1/3 bg-white rounded-2xl p-6 shadow-soft border border-brand-border h-fit">
                    <h2 class="font-bold text-lg text-brand-900 border-b border-brand-border pb-4 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle text-brand-primary"></i>
                        <span><?php echo __('Add New Category'); ?></span>
                    </h2>

                    <form action="categories.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="category-name">
                                <?php echo __('Category Name'); ?> <span class="text-brand-danger">*</span>
                            </label>
                            <input class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-sm font-medium" 
                                   id="category-name" 
                                   name="name" 
                                   placeholder="<?php echo __('e.g., Home Cleaning, AC Repair'); ?>" 
                                   required 
                                   type="text">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2">
                                <?php echo __('Icon'); ?>
                            </label>
                            <input type="hidden" name="icon" id="selectedIconInput" value="fa-solid fa-wrench">
                            
                            <div class="grid grid-cols-4 gap-2 border border-brand-border rounded-xl p-2 bg-brand-surface/40 max-h-56 overflow-y-auto" id="iconPickerGrid">
                                <?php foreach ($available_icons as $index => $item): ?>
                                    <button 
                                        type="button"
                                        class="icon-choice flex flex-col items-center justify-center p-2.5 rounded-xl border text-sm transition-all <?php echo $index === 0 ? 'border-brand-primary bg-brand-primaryLight text-brand-primary ring-2 ring-brand-primary/30 font-bold shadow-xs' : 'border-brand-border bg-white text-brand-text hover:border-brand-primary/40 hover:bg-brand-surface'; ?>"
                                        data-icon="<?php echo htmlspecialchars($item['icon']); ?>"
                                        title="<?php echo htmlspecialchars($item['label']); ?>"
                                        onclick="selectCategoryIcon('<?php echo htmlspecialchars($item['icon']); ?>', this)"
                                    >
                                        <i class="<?php echo htmlspecialchars($item['icon']); ?> text-base mb-1"></i>
                                        <span class="text-[9px] leading-tight text-center text-brand-textMuted line-clamp-1 w-full"><?php echo htmlspecialchars($item['label']); ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" name="add_category" class="w-full bg-brand-primary hover:bg-brand-primaryHover text-white font-bold py-3 rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 mt-4">
                            <i class="fa-solid fa-plus"></i>
                            <span><?php echo __('Add Category'); ?></span>
                        </button>
                    </form>
                </section>

                <!-- Existing Categories Grid -->
                <section class="w-full xl:w-2/3 bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                    <div class="bg-brand-surface/60 border-b border-brand-border px-6 py-4 flex items-center justify-between">
                        <h2 class="font-bold text-base text-brand-900"><?php echo __('Existing Categories'); ?></h2>
                        <span class="text-xs font-bold bg-white px-3 py-1 rounded-full text-brand-primary border border-brand-border shadow-2xs">
                            <?php echo count($categories); ?> <?php echo __('Total'); ?>
                        </span>
                    </div>

                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                                <?php $cat_icon = getCategoryFAIcon($category['name'], $category['icon'] ?? ''); ?>
                                <div class="flex items-center justify-between p-4 rounded-xl border border-brand-border hover:border-brand-primary/40 hover:shadow-xs transition-all bg-brand-surface/20 group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-white shadow-2xs flex items-center justify-center text-brand-primary border border-brand-border group-hover:scale-105 transition-all">
                                            <i class="<?php echo htmlspecialchars($cat_icon); ?> text-lg"></i>
                                        </div>
                                        <span class="font-bold text-brand-900 text-sm"><?php echo htmlspecialchars($category['name']); ?></span>
                                    </div>
                                    <a href="categories.php?delete=1&id=<?php echo (int) $category['id']; ?>" 
                                       class="w-8 h-8 rounded-lg text-brand-textMuted hover:bg-rose-50 hover:text-rose-700 flex items-center justify-center transition-colors" 
                                       title="<?php echo __('Delete Category'); ?>" 
                                       onclick="return confirm('<?php echo htmlspecialchars(__('Delete this category?'), ENT_QUOTES); ?>')">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-2 py-8 text-center">
                                <?php echo renderEmptyState('fa-solid fa-tags', 'No categories added yet.'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<script>
function selectCategoryIcon(iconClass, buttonEl) {
    document.getElementById('selectedIconInput').value = iconClass;
    document.querySelectorAll('#iconPickerGrid .icon-choice').forEach(function(btn) {
        btn.className = 'icon-choice flex flex-col items-center justify-center p-2.5 rounded-xl border text-sm transition-all border-brand-border bg-white text-brand-text hover:border-brand-primary/40 hover:bg-brand-surface';
    });
    buttonEl.className = 'icon-choice flex flex-col items-center justify-center p-2.5 rounded-xl border text-sm transition-all border-brand-primary bg-brand-primaryLight text-brand-primary ring-2 ring-brand-primary/30 font-bold shadow-xs';
}
</script>
</body>
</html>