<?php
/**
 * Dabberha (دبرها) - User Registration
 * Location: public/register.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/db/users_db.php';

// Redirect logged-in users
if (isLoggedIn()) {
    if (getUserRole() === 'admin') {
        header('Location: /local-services-platform/admin/dashboard.php');
    } elseif (getUserRole() === 'provider') {
        header('Location: /local-services-platform/provider/dashboard.php');
    } else {
        header('Location: /local-services-platform/index.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!in_array($role, ['customer', 'provider'], true)) {
        $error = 'Invalid account type';
    } else {
        if (getUserByEmail($email)) {
            $error = 'Email already registered';
        } else {
            $created_id = createUser([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'phone' => $phone
            ]);

            if ($created_id > 0) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

renderHead(['title' => __('Create Account') . ' - ' . __('Dabberha')]);
?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-background">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="/local-services-platform/index.php" class="inline-flex items-center gap-2 text-3xl font-black text-primary tracking-tight">
            <span><?php echo __('Dabberha'); ?></span>
        </a>
        <h2 class="mt-4 text-2xl font-bold text-on-background"><?php echo __('Create a new account'); ?></h2>
        <p class="mt-1 text-sm text-on-surface-variant"><?php echo __('Join Dabberha as a client or verified service provider'); ?></p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-surface-container-lowest py-8 px-6 sm:px-10 shadow-ambient border border-surface-variant rounded-2xl">
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
                    <a href="login.php" class="w-full inline-flex items-center justify-center bg-primary hover:bg-[#7a2f18] text-white font-bold py-3 rounded-xl text-sm shadow-ambient transition-all">
                        <span><?php echo __('Proceed to Login'); ?></span>
                        <i class="fa-solid fa-arrow-right mr-2"></i>
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <!-- Role Selection Radio Cards -->
                    <div>
                        <label class="block text-xs font-bold text-on-background mb-2"><?php echo __('I want to'); ?> <span class="text-error">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="border border-outline-variant rounded-xl p-3.5 flex flex-col items-center justify-center cursor-pointer hover:border-primary/50 transition-all text-center has-checked:border-primary has-checked:bg-primary-fixed/30 has-checked:text-primary">
                                <input type="radio" name="role" value="customer" class="sr-only" <?php echo (!isset($_POST['role']) || $_POST['role'] === 'customer') ? 'checked' : ''; ?>>
                                <i class="fa-regular fa-user text-xl mb-1.5"></i>
                                <span class="text-xs font-bold"><?php echo __('Find Services'); ?></span>
                                <span class="text-[10px] text-on-surface-variant"><?php echo __('Customer'); ?></span>
                            </label>

                            <label class="border border-outline-variant rounded-xl p-3.5 flex flex-col items-center justify-center cursor-pointer hover:border-primary/50 transition-all text-center has-checked:border-primary has-checked:bg-primary-fixed/30 has-checked:text-primary">
                                <input type="radio" name="role" value="provider" class="sr-only" <?php echo (isset($_POST['role']) && $_POST['role'] === 'provider') ? 'checked' : ''; ?>>
                                <i class="fa-solid fa-briefcase text-xl mb-1.5"></i>
                                <span class="text-xs font-bold"><?php echo __('Offer Services'); ?></span>
                                <span class="text-[10px] text-on-surface-variant"><?php echo __('Service Provider'); ?></span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-bold text-on-background mb-1"><?php echo __('Full Name'); ?> <span class="text-error">*</span></label>
                        <input id="name" name="name" type="text" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-on-background mb-1"><?php echo __('Email Address'); ?> <span class="text-error">*</span></label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold text-on-background mb-1"><?php echo __('Phone Number'); ?></label>
                        <input id="phone" name="phone" type="tel" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+966 50 000 0000"
                               class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-bold text-on-background mb-1"><?php echo __('Password'); ?> <span class="text-error">*</span></label>
                            <input id="password" name="password" type="password" required placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-xs font-bold text-on-background mb-1"><?php echo __('Confirm Password'); ?> <span class="text-error">*</span></label>
                            <input id="confirm_password" name="confirm_password" type="password" required placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-[#7a2f18] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-ambient flex items-center justify-center gap-2 mt-4">
                        <i class="fa-solid fa-user-plus"></i>
                        <span><?php echo __('Create Account'); ?></span>
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 pt-6 border-t border-surface-variant text-center space-y-3">
                <p class="text-xs text-on-surface-variant">
                    <?php echo __('Already have an account?'); ?>
                    <a href="login.php" class="font-bold text-primary hover:underline"><?php echo __('Sign in'); ?></a>
                </p>
                <div>
                    <a href="/local-services-platform/index.php" class="text-xs text-on-surface-variant hover:text-primary transition-colors">
                        ← <?php echo __('Back to Home'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>