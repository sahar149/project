<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// إذا كان المستخدم مسجل دخوله بالفعل، حوله للصفحة المناسبة
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

    // التحقق من صحة المدخلات
    if (empty($name) || empty($email) || empty($password)) {

        $error = 'Please fill all required fields';

    } elseif ($password !== $confirm_password) {

        $error = 'Passwords do not match';

    } elseif (strlen($password) < 6) {

        $error = 'Password must be at least 6 characters';

    } elseif (!in_array($role, ['customer', 'provider'])) {

        $error = 'Invalid account type';

    } else {

        // التحقق إذا كان الإيميل موجود مسبقاً
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $error = 'Email already registered';

        } else {

            // تشفير كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // إدخال المستخدم
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, phone)
                VALUES (?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([
                $name,
                $email,
                $hashed_password,
                $role,
                $phone
            ])) {

                $success = 'Registration successful! You can now login.';

            } else {

                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account - Dabberha</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Material Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
        rel="stylesheet"
    >

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: "#F9F5F1",
                        surface: "#FFFFFF",
                        onSurface: "#3A2F2B",
                        primary: "#CB6D51",
                        secondary: "#C18B8B",
                        outline: "#DBC1BA"
                    },

                    fontFamily: {
                        jakarta: ["Plus Jakarta Sans", "sans-serif"]
                    }
                }
            }
        }
    </script>

    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }

        body {
            background-color: #F9F5F1;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-[#3A2F2B]">

    <!-- =========================
         NAVBAR
    ========================== -->
    <header
        class="bg-[#F9F5F1] border-b border-[#C18B8B]/20 shadow-sm sticky top-0 z-50"
    >

        <nav
            class="flex justify-between items-center w-full px-6 py-4 max-w-7xl mx-auto"
        >

            <!-- Logo -->
            <a
                href="../index.php"
                class="text-2xl font-extrabold text-[#CB6D51]"
            >
                Dabberha
            </a>

            <!-- Navigation -->
            <div class="hidden md:flex items-center space-x-8">

                <a
                    href="../index.php"
                    class="font-medium text-[#3A2F2B] hover:text-[#CB6D51] transition"
                >
                    Find Services
                </a>

                <a
                    href="#"
                    class="font-medium text-[#3A2F2B] hover:text-[#CB6D51] transition"
                >
                    How it Works
                </a>

                <a
                    href="#"
                    class="font-medium text-[#3A2F2B] hover:text-[#CB6D51] transition"
                >
                    Community
                </a>

            </div>

            <!-- Login -->
            <div class="flex items-center">

                <a
                    href="login.php"
                    class="bg-[#CB6D51] text-white px-6 py-2.5 rounded-full font-semibold hover:brightness-105 transition"
                >
                    Log In
                </a>

            </div>

        </nav>

    </header>


    <!-- =========================
         MAIN
    ========================== -->

    <main
        class="flex-grow flex items-center justify-center py-12 md:py-20 px-4"
    >

        <div
            class="w-full max-w-[560px] bg-white rounded-2xl shadow-[0_4px_20px_rgba(58,47,43,0.05)] p-7 md:p-12 border border-[#C18B8B]/10"
        >

            <!-- Header -->
            <div class="text-center mb-8">

                <h1
                    class="text-3xl md:text-4xl font-bold text-[#3A2F2B] mb-3"
                >
                    Create Your Account
                </h1>

                <p class="text-[#3A2F2B]/60">
                    Join Dabberha and find trusted local services.
                </p>

            </div>


            <!-- =========================
                 ALERTS
            ========================== -->

            <?php if ($error): ?>

                <div
                    class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm"
                >
                    <div class="flex items-center gap-2">

                        <span class="material-symbols-outlined text-[20px]">
                            error
                        </span>

                        <span>
                            <?php echo htmlspecialchars($error); ?>
                        </span>

                    </div>
                </div>

            <?php endif; ?>


            <?php if ($success): ?>

                <div
                    class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm"
                >
                    <div class="flex items-center gap-2">

                        <span class="material-symbols-outlined text-[20px]">
                            check_circle
                        </span>

                        <span>
                            <?php echo htmlspecialchars($success); ?>
                        </span>

                    </div>
                </div>

            <?php endif; ?>


            <!-- =========================
                 FORM
            ========================== -->

            <form method="POST" class="space-y-6">


                <!-- Account Type -->

                <div>

                    <label
                        class="block font-semibold text-sm mb-2"
                    >
                        Account Type
                    </label>

                    <div
                        class="flex p-1 bg-[#F9F5F1] rounded-lg"
                    >

                        <button
                            type="button"
                            id="customerBtn"
                            onclick="selectRole('customer')"
                            class="flex-1 py-3 text-sm font-semibold rounded-md transition-all bg-[#C18B8B] text-white"
                        >
                            Customer
                        </button>

                        <button
                            type="button"
                            id="providerBtn"
                            onclick="selectRole('provider')"
                            class="flex-1 py-3 text-sm font-semibold rounded-md transition-all text-[#3A2F2B]/60"
                        >
                            Service Provider
                        </button>

                    </div>

                    <input
                        type="hidden"
                        name="role"
                        id="role"
                        value="<?php echo htmlspecialchars($_POST['role'] ?? 'customer'); ?>"
                    >

                </div>


                <!-- Full Name -->

                <div>

                    <label
                        class="block font-semibold text-sm mb-2"
                    >
                        Full Name <span class="text-[#CB6D51]">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        placeholder="Enter your full name"
                        required
                        class="w-full bg-white border border-[#DBC1BA] rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#C18B8B] focus:border-transparent outline-none transition placeholder:text-[#3A2F2B]/30"
                    >

                </div>


                <!-- Email -->

                <div>

                    <label
                        class="block font-semibold text-sm mb-2"
                    >
                        Email Address <span class="text-[#CB6D51]">*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        placeholder="you@example.com"
                        required
                        class="w-full bg-white border border-[#DBC1BA] rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#C18B8B] focus:border-transparent outline-none transition placeholder:text-[#3A2F2B]/30"
                    >

                </div>


                <!-- Phone + Password -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Phone -->

                    <div>

                        <label
                            class="block font-semibold text-sm mb-2"
                        >
                            Phone Number
                        </label>

                        <input
                            type="tel"
                            name="phone"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            placeholder="Enter phone number"
                            class="w-full bg-white border border-[#DBC1BA] rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#C18B8B] focus:border-transparent outline-none transition placeholder:text-[#3A2F2B]/30"
                        >

                    </div>


                    <!-- Password -->

                    <div>

                        <label
                            class="block font-semibold text-sm mb-2"
                        >
                            Password <span class="text-[#CB6D51]">*</span>
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            minlength="6"
                            required
                            class="w-full bg-white border border-[#DBC1BA] rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#C18B8B] focus:border-transparent outline-none transition placeholder:text-[#3A2F2B]/30"
                        >

                    </div>

                </div>


                <!-- Confirm Password -->

                <div>

                    <label
                        class="block font-semibold text-sm mb-2"
                    >
                        Confirm Password <span class="text-[#CB6D51]">*</span>
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        minlength="6"
                        required
                        class="w-full bg-white border border-[#DBC1BA] rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#C18B8B] focus:border-transparent outline-none transition placeholder:text-[#3A2F2B]/30"
                    >

                    <p class="text-xs text-[#3A2F2B]/50 mt-2">
                        Password must contain at least 6 characters.
                    </p>

                </div>


                <!-- Register Button -->

                <div class="pt-2">

                    <button
                        type="submit"
                        class="w-full bg-[#CB6D51] text-white font-semibold py-4 rounded-lg shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                    >

                        <span>
                            Create Account
                        </span>

                        <span class="material-symbols-outlined text-[20px]">
                            arrow_forward
                        </span>

                    </button>

                </div>


                <!-- Terms -->

                <p
                    class="text-center text-xs text-[#3A2F2B]/60 leading-5"
                >
                    By registering, you agree to our

                    <a
                        href="#"
                        class="text-[#CB6D51] font-semibold hover:underline"
                    >
                        Terms of Service
                    </a>

                    and

                    <a
                        href="#"
                        class="text-[#CB6D51] font-semibold hover:underline"
                    >
                        Privacy Policy
                    </a>.
                </p>

            </form>


            <!-- =========================
                 LOGIN
            ========================== -->

            <div class="relative my-8">

                <div class="absolute inset-0 flex items-center">

                    <div
                        class="w-full border-t border-[#C18B8B]/20"
                    ></div>

                </div>

                <div class="relative flex justify-center">

                    <span
                        class="px-4 bg-white text-sm text-[#3A2F2B]/40 font-medium"
                    >
                        Already have an account?
                    </span>

                </div>

            </div>


            <div class="text-center">

                <a
                    href="login.php"
                    class="inline-flex items-center justify-center w-full py-3 border border-[#DBC1BA] rounded-lg text-[#CB6D51] font-semibold hover:bg-[#F9F5F1] transition"
                >
                    Log In
                </a>

            </div>

        </div>

    </main>


    <!-- =========================
         FOOTER
    ========================== -->

    <footer
        class="bg-[#F9F5F1] border-t border-stone-200"
    >

        <div
            class="flex flex-col md:flex-row justify-between items-center w-full px-8 py-8 max-w-7xl mx-auto gap-4"
        >

            <div class="text-center md:text-left">

                <div
                    class="text-lg font-bold text-[#CB6D51]"
                >
                    Dabberha
                </div>

                <p class="text-sm text-[#3A2F2B]/60 mt-1">
                    © 2026 Dabberha Local Services. All rights reserved.
                </p>

            </div>


            <div class="flex flex-wrap justify-center gap-6">

                <a
                    href="#"
                    class="text-sm text-[#3A2F2B]/70 hover:text-[#CB6D51] transition"
                >
                    Privacy Policy
                </a>

                <a
                    href="#"
                    class="text-sm text-[#3A2F2B]/70 hover:text-[#CB6D51] transition"
                >
                    Terms of Service
                </a>

                <a
                    href="#"
                    class="text-sm text-[#3A2F2B]/70 hover:text-[#CB6D51] transition"
                >
                    Help Center
                </a>

                <a
                    href="#"
                    class="text-sm text-[#3A2F2B]/70 hover:text-[#CB6D51] transition"
                >
                    Contact Us
                </a>

            </div>

        </div>

    </footer>


    <!-- =========================
         ROLE SELECTOR
    ========================== -->

    <script>

        function selectRole(role) {

            const roleInput = document.getElementById('role');

            const customerBtn = document.getElementById('customerBtn');

            const providerBtn = document.getElementById('providerBtn');

            roleInput.value = role;


            if (role === 'customer') {

                customerBtn.classList.add(
                    'bg-[#C18B8B]',
                    'text-white'
                );

                customerBtn.classList.remove(
                    'text-[#3A2F2B]/60'
                );


                providerBtn.classList.remove(
                    'bg-[#C18B8B]',
                    'text-white'
                );

                providerBtn.classList.add(
                    'text-[#3A2F2B]/60'
                );

            } else {

                providerBtn.classList.add(
                    'bg-[#C18B8B]',
                    'text-white'
                );

                providerBtn.classList.remove(
                    'text-[#3A2F2B]/60'
                );


                customerBtn.classList.remove(
                    'bg-[#C18B8B]',
                    'text-white'
                );

                customerBtn.classList.add(
                    'text-[#3A2F2B]/60'
                );
            }
        }


        // الحفاظ على الاختيار بعد إعادة تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function () {

            const currentRole =
                document.getElementById('role').value;

            selectRole(currentRole);

        });

    </script>

</body>

</html>