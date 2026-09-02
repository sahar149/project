<?php
/**
 * Dabberha (دبرها) - Provider Profile
 * Location: provider/profile.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/users_db.php';

requireRole('provider');

$provider_id = getUserId();
$user = getUserById($provider_id);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Name is required';
    } else {
        if (updateUserProfile($provider_id, ['name' => $name, 'phone' => $phone, 'address' => $address])) {
            $success = 'Profile updated successfully!';
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['address'] = $address;
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

renderHead(['title' => __('Edit Profile - Provider')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-4xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                    <i class="fa-regular fa-user text-brand-primary"></i>
                    <span><?php echo __('My Profile & Settings'); ?></span>
                </h1>
                <p class="text-brand-textMuted mt-1"><?php echo __('Manage your personal contact details and business address'); ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <?php echo renderAlert($error, 'danger'); ?>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <?php echo renderAlert($success, 'success'); ?>
            <?php endif; ?>

            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-brand-border">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="name">
                                <?php echo __('Full Name'); ?> <span class="text-brand-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="email">
                                <?php echo __('Email Address'); ?>
                            </label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-gray-100 text-gray-500 text-sm font-medium cursor-not-allowed" disabled>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="phone">
                                <?php echo __('Phone Number'); ?>
                            </label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" placeholder="+966 50 000 0000">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-brand-text mb-2" for="address">
                                <?php echo __('Location / City'); ?>
                            </label>
                            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" placeholder="<?php echo __('e.g., Riyadh, Olaya'); ?>">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-brand-border flex justify-end">
                        <button type="submit" class="bg-brand-primary hover:bg-brand-primaryHover text-white px-6 py-2.5 rounded-xl text-sm font-bold transition-all shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span><?php echo __('Save Profile'); ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>