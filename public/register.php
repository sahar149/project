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
                        <label class="block text-xs font-bold text-on-background mb-2"><?php echo __('Account Type'); ?> <span class="text-error">*</span></label>
                        <div class="grid grid-cols-2 gap-3" id="roleContainer">
                            <label id="label-customer" class="relative border-2 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all text-center select-none <?php echo (!isset($_POST['role']) || $_POST['role'] === 'customer') ? 'border-primary bg-[#fff1ec] text-primary shadow-xs' : 'border-outline-variant/60 bg-surface-container-lowest text-on-surface-variant hover:border-outline-variant'; ?>">
                                <input type="radio" name="role" value="customer" id="role-customer" class="sr-only" <?php echo (!isset($_POST['role']) || $_POST['role'] === 'customer') ? 'checked' : ''; ?>>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 <?php echo (!isset($_POST['role']) || $_POST['role'] === 'customer') ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant'; ?>" id="icon-customer">
                                    <i class="fa-regular fa-user text-base"></i>
                                </div>
                                <span class="text-xs font-bold text-on-background"><?php echo __('Customer'); ?></span>
                                <span class="text-[11px] text-on-surface-variant mt-0.5"><?php echo __('Find Services'); ?></span>
                                <span class="absolute top-2 left-2 text-primary <?php echo (!isset($_POST['role']) || $_POST['role'] === 'customer') ? '' : 'hidden'; ?>" id="check-customer">
                                    <i class="fa-solid fa-circle-check text-sm"></i>
                                </span>
                            </label>

                            <label id="label-provider" class="relative border-2 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all text-center select-none <?php echo (isset($_POST['role']) && $_POST['role'] === 'provider') ? 'border-primary bg-[#fff1ec] text-primary shadow-xs' : 'border-outline-variant/60 bg-surface-container-lowest text-on-surface-variant hover:border-outline-variant'; ?>">
                                <input type="radio" name="role" value="provider" id="role-provider" class="sr-only" <?php echo (isset($_POST['role']) && $_POST['role'] === 'provider') ? 'checked' : ''; ?>>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 <?php echo (isset($_POST['role']) && $_POST['role'] === 'provider') ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant'; ?>" id="icon-provider">
                                    <i class="fa-solid fa-briefcase text-base"></i>
                                </div>
                                <span class="text-xs font-bold text-on-background"><?php echo __('Service Provider'); ?></span>
                                <span class="text-[11px] text-on-surface-variant mt-0.5"><?php echo __('Offer Services'); ?></span>
                                <span class="absolute top-2 left-2 text-primary <?php echo (isset($_POST['role']) && $_POST['role'] === 'provider') ? '' : 'hidden'; ?>" id="check-provider">
                                    <i class="fa-solid fa-circle-check text-sm"></i>
                                </span>
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
                            <label for="password" class="block text-xs font-bold text-on-background mb-1">
                                <?php echo __('Password'); ?> <span class="text-error">*</span>
                            </label>
                            <input id="password" name="password" type="password" minlength="6" required placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span id="pass-hint" class="text-[11px] text-on-surface-variant block mt-1">
                                <i class="fa-solid fa-circle-info text-[10px] ml-1"></i><?php echo __('Must be at least 6 characters'); ?>
                            </span>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-xs font-bold text-on-background mb-1">
                                <?php echo __('Confirm Password'); ?> <span class="text-error">*</span>
                            </label>
                            <input id="confirm_password" name="confirm_password" type="password" minlength="6" required placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span id="match-hint" class="text-[11px] text-on-surface-variant block mt-1"></span>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn" class="w-full bg-primary hover:bg-[#7a2f18] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-ambient flex items-center justify-center gap-2 mt-4">
                        <i class="fa-solid fa-user-plus"></i>
                        <span><?php echo __('Create Account'); ?></span>
                    </button>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Role Selection Interactive Logic
                    const roleCustomer = document.getElementById('role-customer');
                    const roleProvider = document.getElementById('role-provider');
                    const labelCustomer = document.getElementById('label-customer');
                    const labelProvider = document.getElementById('label-provider');
                    const iconCustomer = document.getElementById('icon-customer');
                    const iconProvider = document.getElementById('icon-provider');
                    const checkCustomer = document.getElementById('check-customer');
                    const checkProvider = document.getElementById('check-provider');

                    function updateRoleUI() {
                        if (roleCustomer && roleCustomer.checked) {
                            labelCustomer.className = 'relative border-2 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all text-center select-none border-primary bg-[#fff1ec] text-primary shadow-xs';
                            iconCustomer.className = 'w-10 h-10 rounded-full flex items-center justify-center mb-2 bg-primary text-white';
                            checkCustomer.classList.remove('hidden');

                            labelProvider.className = 'relative border-2 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all text-center select-none border-outline-variant/60 bg-surface-container-lowest text-on-surface-variant hover:border-outline-variant';
                            iconProvider.className = 'w-10 h-10 rounded-full flex items-center justify-center mb-2 bg-surface-container text-on-surface-variant';
                            checkProvider.classList.add('hidden');
                        } else if (roleProvider && roleProvider.checked) {
                            labelProvider.className = 'relative border-2 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all text-center select-none border-primary bg-[#fff1ec] text-primary shadow-xs';
                            iconProvider.className = 'w-10 h-10 rounded-full flex items-center justify-center mb-2 bg-primary text-white';
                            checkProvider.classList.remove('hidden');

                            labelCustomer.className = 'relative border-2 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all text-center select-none border-outline-variant/60 bg-surface-container-lowest text-on-surface-variant hover:border-outline-variant';
                            iconCustomer.className = 'w-10 h-10 rounded-full flex items-center justify-center mb-2 bg-surface-container text-on-surface-variant';
                            checkCustomer.classList.add('hidden');
                        }
                    }

                    if (labelCustomer && labelProvider) {
                        labelCustomer.addEventListener('click', function() {
                            roleCustomer.checked = true;
                            updateRoleUI();
                        });
                        labelProvider.addEventListener('click', function() {
                            roleProvider.checked = true;
                            updateRoleUI();
                        });
                    }

                    // Password Validation Logic
                    const pass = document.getElementById('password');
                    const confirmPass = document.getElementById('confirm_password');
                    const passHint = document.getElementById('pass-hint');
                    const matchHint = document.getElementById('match-hint');

                    if (pass) {
                        pass.addEventListener('input', function() {
                            if (this.value.length > 0 && this.value.length < 6) {
                                passHint.className = 'text-[11px] text-red-600 font-semibold block mt-1';
                                passHint.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-[10px] ml-1"></i> <?php echo __('Password must be at least 6 characters'); ?> (' + this.value.length + '/6)';
                            } else if (this.value.length >= 6) {
                                passHint.className = 'text-[11px] text-green-600 font-semibold block mt-1';
                                passHint.innerHTML = '<i class="fa-solid fa-check text-[10px] ml-1"></i> <?php echo __('Password meets minimum requirements'); ?>';
                            } else {
                                passHint.className = 'text-[11px] text-on-surface-variant block mt-1';
                                passHint.innerHTML = '<i class="fa-solid fa-circle-info text-[10px] ml-1"></i> <?php echo __('Must be at least 6 characters'); ?>';
                            }
                            checkMatch();
                        });
                    }

                    if (confirmPass) {
                        confirmPass.addEventListener('input', checkMatch);
                    }

                    function checkMatch() {
                        if (!confirmPass || confirmPass.value.length === 0) {
                            matchHint.innerHTML = '';
                            return;
                        }
                        if (pass && confirmPass.value === pass.value) {
                            matchHint.className = 'text-[11px] text-green-600 font-semibold block mt-1';
                            matchHint.innerHTML = '<i class="fa-solid fa-check text-[10px] ml-1"></i> <?php echo __('Passwords match'); ?>';
                        } else {
                            matchHint.className = 'text-[11px] text-red-600 font-semibold block mt-1';
                            matchHint.innerHTML = '<i class="fa-solid fa-xmark text-[10px] ml-1"></i> <?php echo __('Passwords do not match'); ?>';
                        }
                    }
                });
                </script>
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