<?php
/**
 * Dabberha (دبرها) - Login Page
 * Location: public/login.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/db/users_db.php';

$requested_role = $_GET['role'] ?? '';
$return_url = $_GET['return_url'] ?? '';
$valid_roles = ['admin', 'provider', 'customer'];
$requested_role = in_array($requested_role, $valid_roles, true) ? $requested_role : '';
$return_url = filter_var($return_url, FILTER_SANITIZE_URL);

// Redirect already logged-in users
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $requested_role = $_POST['role'] ?? $requested_role;
    $return_url = $_POST['return_url'] ?? $return_url;
    $requested_role = in_array($requested_role, $valid_roles, true) ? $requested_role : '';
    $return_url = filter_var($return_url, FILTER_SANITIZE_URL);

    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        $user = getUserByEmail($email);

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password'])) {
            if ($requested_role === 'admin' && $user['role'] !== 'admin') {
                $error = 'Please login with an admin account.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];

                if ($requested_role === $user['role'] && !empty($return_url)) {
                    header("Location: $return_url");
                } elseif ($user['role'] === 'admin') {
                    header('Location: /local-services-platform/admin/dashboard.php');
                } elseif ($user['role'] === 'provider') {
                    header('Location: /local-services-platform/provider/dashboard.php');
                } else {
                    header('Location: /local-services-platform/index.php');
                }
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

renderHead(['title' => __('Login') . ' - ' . __('Dabberha')]);
?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-background">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="/local-services-platform/index.php" class="inline-flex items-center gap-2 text-3xl font-black text-primary tracking-tight">
            <span><?php echo __('Dabberha'); ?></span>
        </a>
        <h2 class="mt-4 text-2xl font-bold text-on-background"><?php echo __('Sign in to your account'); ?></h2>
        <p class="mt-1 text-sm text-on-surface-variant"><?php echo __('Welcome back! Access your services, bookings, and dashboard'); ?></p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-surface-container-lowest py-8 px-6 sm:px-10 shadow-ambient border border-surface-variant rounded-2xl">
            <?php if (!empty($error)): ?>
                <div class="mb-6">
                    <?php echo renderAlert($error, 'danger'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($requested_role); ?>">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">

                <div>
                    <label for="email" class="block text-xs font-bold text-on-background mb-1.5"><?php echo __('Email Address'); ?> <span class="text-error">*</span></label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="you@example.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-on-background"><?php echo __('Password'); ?> <span class="text-error">*</span></label>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-[#7a2f18] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-ambient flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span><?php echo __('Sign In'); ?></span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-surface-variant text-center space-y-3">
                <p class="text-xs text-on-surface-variant">
                    <?php echo __("Don't have an account?"); ?>
                    <a href="register.php" class="font-bold text-primary hover:underline"><?php echo __('Sign up now'); ?></a>
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