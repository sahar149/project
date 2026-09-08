<?php
/**
 * Dabberha (دبرها) - Admin Registration Utility
 * Location: public/register-admin.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/db/users_db.php';

// Secret key
$secret_key = 'admin123';

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die(__('Access denied. Invalid key.'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = __('Please fill all required fields');
    } elseif ($password !== $confirm_password) {
        $error = __('Passwords do not match');
    } elseif (strlen($password) < 6) {
        $error = __('Password must be at least 6 characters');
    } else {
        if (getUserByEmail($email)) {
            $error = __('Email already registered');
        } else {
            $created_id = createUser([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'admin'
            ]);

            if ($created_id > 0) {
                $success = __('Admin registered successfully! You can now login.');
            } else {
                $error = __('Registration failed. Please try again.');
            }
        }
    }
}

renderHead(['title' => __('Register Admin') . ' - ' . __('Dabberha')]);
?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-brand-bg">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center mx-auto text-2xl mb-3 shadow-2xs">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h2 class="text-2xl font-bold text-brand-900"><?php echo __('Register System Administrator'); ?></h2>
        <p class="mt-1 text-xs text-brand-textMuted"><?php echo __('Secret Administrator Provisioning'); ?></p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-6 sm:px-10 shadow-soft border border-brand-border rounded-2xl">
            <?php if (!empty($error)): ?>
                <div class="mb-6">
                    <?php echo renderAlert($error, 'danger'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-6">
                    <?php echo renderAlert($success, 'success'); ?>
                </div>
                <div class="text-center pt-2">
                    <a href="login.php?role=admin" class="w-full inline-flex items-center justify-center bg-brand-primary hover:bg-brand-primaryHover text-white font-bold py-3 rounded-xl text-sm shadow-xs transition-all">
                        <span><?php echo __('Go to Admin Login'); ?></span>
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-brand-text mb-1"><?php echo __('Full Name'); ?> <span class="text-brand-danger">*</span></label>
                        <input id="name" name="name" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-brand-text mb-1"><?php echo __('Email Address'); ?> <span class="text-brand-danger">*</span></label>
                        <input id="email" name="email" type="email" required class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-brand-text mb-1"><?php echo __('Password'); ?> <span class="text-brand-danger">*</span></label>
                        <input id="password" name="password" type="password" minlength="6" required class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-xs font-bold text-brand-text mb-1"><?php echo __('Confirm Password'); ?> <span class="text-brand-danger">*</span></label>
                        <input id="confirm_password" name="confirm_password" type="password" minlength="6" required class="w-full px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-medium focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                    </div>

                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-xs flex items-center justify-center gap-2 mt-4">
                        <i class="fa-solid fa-user-shield"></i>
                        <span><?php echo __('Register Administrator'); ?></span>
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 pt-6 border-t border-brand-border text-center">
                <a href="login.php" class="text-xs text-brand-textMuted hover:text-brand-primary transition-colors">
                    <?php echo __('Back to Login'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>