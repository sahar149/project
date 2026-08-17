<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$requested_role = $_GET['role'] ?? '';
$return_url = $_GET['return_url'] ?? '';
$valid_roles = ['admin', 'provider', 'customer'];
$requested_role = in_array($requested_role, $valid_roles, true) ? $requested_role : '';
$return_url = filter_var($return_url, FILTER_SANITIZE_URL);

// إذا كان المستخدم مسجل دخوله بالفعل
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
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $requested_role = $_POST['role'] ?? $requested_role;
    $return_url = $_POST['return_url'] ?? $return_url;
    $requested_role = in_array($requested_role, $valid_roles, true) ? $requested_role : '';
    $return_url = filter_var($return_url, FILTER_SANITIZE_URL);

    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
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
?>

<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<title>Login - Local Services</title>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-fixed": "#1c1c19",
                        "primary-container": "#b45b40",
                        "secondary-container": "#ffc3c2",
                        "inverse-primary": "#ffb59f",
                        "on-secondary-fixed": "#321112",
                        "on-primary-container": "#fffbff",
                        "on-secondary": "#ffffff",
                        "on-surface-variant": "#3A2F2B",
                        "surface-container-high": "#f7e4de",
                        "surface-container": "#fdeae4",
                        "tertiary-container": "#767471",
                        "background": "#F9F5F1",
                        "surface-variant": "#f1dfd8",
                        "on-primary-fixed-variant": "#7a2f18",
                        "secondary": "#C18B8B",
                        "error-container": "#ffdad6",
                        "on-surface": "#3A2F2B",
                        "primary": "#CB6D51",
                        "on-error-container": "#93000a",
                        "inverse-on-surface": "#ffede7",
                        "surface-container-low": "#fff1ec",
                        "on-secondary-container": "#C18B8B",
                        "on-primary-fixed": "#3a0a00",
                        "on-tertiary": "#ffffff",
                        "error": "#ba1a1a",
                        "secondary-fixed": "#ffdad9",
                        "on-tertiary-container": "#fffbff",
                        "on-tertiary-fixed-variant": "#484744",
                        "outline-variant": "#dbc1ba",
                        "tertiary-fixed": "#e6e2de",
                        "surface": "#F9F5F1",
                        "surface-container-lowest": "#ffffff",
                        "on-primary": "#ffffff",
                        "primary-fixed": "#ffdbd1",
                        "on-secondary-fixed-variant": "#653b3c",
                        "outline": "#88726c",
                        "on-background": "#3A2F2B",
                        "primary-fixed-dim": "#ffb59f",
                        "secondary-fixed-dim": "#f3b8b8",
                        "tertiary-fixed-dim": "#c9c6c2",
                        "surface-dim": "#e9d6d0",
                        "surface-container-highest": "#f1dfd8",
                        "inverse-surface": "#392e2a",
                        "surface-tint": "#CB6D51",
                        "surface-bright": "#F9F5F1",
                        "tertiary": "#5d5c59",
                        "on-error": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "16px",
                        "stack-md": "16px",
                        "stack-sm": "8px",
                        "base": "8px",
                        "margin-desktop": "40px",
                        "gutter": "24px",
                        "stack-lg": "32px",
                        "container-max": "1200px"
                    },
                    "fontFamily": {
                        "headline-lg": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"]
                    },
                    "fontSize": {
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "label-lg": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
                    }
                },
            },
        }
    </script>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen flex flex-col">
<!-- TopAppBar -->
<header class="bg-[#F9F5F1] dark:bg-stone-950 border-b border-stone-200 dark:border-stone-800 shadow-sm sticky top-0 z-50">
<div class="flex justify-between items-center w-full px-6 py-4 max-w-7xl mx-auto font-['Plus_Jakarta_Sans'] antialiased">
<div class="text-2xl font-bold text-[#3A2F2B] dark:text-stone-50 tracking-tight">Dabberha</div>
<nav class="hidden md:flex items-center gap-8">
<a class="text-[#3A2F2B] opacity-80 dark:text-stone-400 font-medium hover:text-[#CB6D51] transition-colors duration-200" href="#">Find Services</a>
<a class="text-[#3A2F2B] opacity-80 dark:text-stone-400 font-medium hover:text-[#CB6D51] transition-colors duration-200" href="#">How it Works</a>
<a class="text-[#3A2F2B] opacity-80 dark:text-stone-400 font-medium hover:text-[#CB6D51] transition-colors duration-200" href="#">About</a>
</nav>
<div class="flex items-center gap-4">
<button class="bg-[#CB6D51] text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 active:scale-95 transition-all shadow-sm">
                Sign Up
            </button>
</div>
</div>
</header>
<!-- Main Content: Login Section -->
<main class="flex-grow flex items-center justify-center px-margin-mobile py-16 md:py-24">
<div class="w-full max-w-[440px]">
<!-- Login Card -->
<div class="bg-surface-container-lowest rounded-xl p-8 md:p-10 shadow-[0px_4px_20px_rgba(58,47,43,0.05)] border border-surface-variant/30">
<div class="text-center mb-10">
<h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Welcome Back</h1>
<p class="font-body-md text-body-md text-on-surface opacity-80">Access your local community dashboard.</p>
</div>
<?php if ($error): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
<p class="text-red-700 font-body-md text-body-md"><?php echo htmlspecialchars($error); ?></p>
</div>
<?php endif; ?>
<form method="POST" class="space-y-6">
<div class="space-y-2">
<label class="block font-label-lg text-label-lg text-on-surface" for="email">Email</label>
<input class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-[#C18B8B] focus:border-[#C18B8B] outline-none transition-all placeholder:text-outline/50 text-on-surface" id="email" name="email" placeholder="neighbor@example.com" type="email" required/>
</div>
<div class="space-y-2">
<div class="flex justify-between items-center">
<label class="block font-label-lg text-label-lg text-on-surface" for="password">Password</label>
<a class="font-label-sm text-label-sm text-[#C18B8B] hover:underline" href="#">Forgot Password?</a>
</div>
<div class="relative">
<input class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-[#C18B8B] focus:border-[#C18B8B] outline-none transition-all placeholder:text-outline/50 text-on-surface" id="password" name="password" placeholder="••••••••" type="password" required/>
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-outline-variant hover:text-[#C18B8B] transition-colors" type="button" onclick="togglePasswordVisibility()">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0; font-size: 20px;">visibility</span>
</button>
</div>
</div>
<div class="flex items-center gap-2 py-2">
<input class="w-4 h-4 rounded border-outline-variant text-[#CB6D51] focus:ring-[#CB6D51]" id="remember" name="remember" type="checkbox"/>
<label class="font-label-sm text-label-sm text-on-surface opacity-70" for="remember">Remember me for 30 days</label>
</div>
<button class="w-full bg-[#CB6D51] text-on-primary py-4 rounded-full font-label-lg text-label-lg shadow-lg hover:shadow-xl active:scale-[0.98] transition-all" type="submit">
                    Login
                </button>
</form>
<div class="mt-10 pt-8 border-t border-surface-variant/50">
<div class="text-center">
<p class="font-body-md text-body-md text-on-surface opacity-80">
                        New to Dabberha? 
                        <a class="text-[#C18B8B] font-semibold hover:underline" href="register.php">Create an account</a>
</p>
</div>
</div>
</div>
<!-- Trust Indicator -->
<div class="mt-8 flex justify-center items-center gap-6 opacity-60 grayscale group hover:grayscale-0 hover:opacity-100 transition-all duration-500">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[#CB6D51]" style="font-variation-settings: 'FILL' 1;">verified_user</span>
<span class="font-label-sm text-label-sm uppercase tracking-wider text-[#3A2F2B]">Secure Access</span>
</div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[#CB6D51]" style="font-variation-settings: 'FILL' 1;">favorite</span>
<span class="font-label-sm text-label-sm uppercase tracking-wider text-[#3A2F2B]">Community First</span>
</div>
</div>
</div>
</main>
<!-- Footer -->
<footer class="bg-white dark:bg-stone-900 border-t border-stone-100 dark:border-stone-800">
<div class="flex flex-col md:flex-row justify-between items-center px-8 py-12 gap-6 w-full max-w-7xl mx-auto font-['Plus_Jakarta_Sans'] text-sm">
<div class="flex flex-col items-center md:items-start gap-2">
<div class="text-lg font-bold text-[#3A2F2B] dark:text-stone-100">Dabberha</div>
<p class="text-[#3A2F2B] opacity-60 dark:text-stone-400">© 2026 Dabberha Services. Built for the community.</p>
</div>
<div class="flex gap-8">
<a class="text-[#3A2F2B] opacity-60 dark:text-stone-400 hover:text-[#CB6D51] dark:hover:text-stone-100 transition-opacity hover:opacity-100" href="#">Privacy Policy</a>
<a class="text-[#3A2F2B] opacity-60 dark:text-stone-400 hover:text-[#CB6D51] dark:hover:text-stone-100 transition-opacity hover:opacity-100" href="#">Terms of Service</a>
<a class="text-[#3A2F2B] opacity-60 dark:text-stone-400 hover:text-[#CB6D51] dark:hover:text-stone-100 transition-opacity hover:opacity-100" href="#">Contact Us</a>
</div>
</div>
</footer>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const visibilityBtn = document.querySelector('button[type="button"]');
    const icon = visibilityBtn.querySelector('.material-symbols-outlined');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>
</body></html>