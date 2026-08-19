<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();
$error = '';
$success = '';

// جلب بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$provider_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($name)) {
        $error = 'Name is required';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $address, $provider_id])) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Provider</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            cream: '#F9F5F1',
                            terracotta: '#CB6D51',
                            rose: '#C18B8B',
                            dark: '#333333',
                            gray: '#666666',
                            lightGray: '#F3F4F6',
                            border: '#E5E7EB',
                        }
                    },
                    borderRadius: {
                        '8xl': '1rem',
                    }
                }
            }
        }
    </script>
    <style data-purpose="custom-styles">
        body {
            background-color: #F9F5F1;
            color: #333333;
        }
        
        input:focus, textarea:focus {
            --tw-ring-color: #CB6D51 !important;
            border-color: #CB6D51 !important;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen font-sans">
    <!-- BEGIN: Top Navbar -->
    <header class="bg-white border-b border-brand-border h-16 flex items-center justify-between px-6 sticky top-0 z-50">
        <div class="flex items-center gap-2 text-brand-terracotta font-bold text-xl">
            <svg fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>LocalEase</span>
        </div>
        <div class="flex items-center gap-6">
            <button class="relative text-brand-gray hover:text-brand-terracotta transition-colors">
                <svg fill="none" height="20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
            <div class="flex items-center gap-3 border-l border-brand-border pl-6 cursor-pointer">
                <img alt="User Avatar" class="w-8 h-8 rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDvSqqz3bX9MUSyzN9ZvlObT5lr64d4hG-DEbje2rguJiGtPhYq9x7GAfcpgz-k9gDXMXIDVtS96q4n4peZzyfGJW_aJCDhJgAB0ZyLsHWyC_cBkxe3GOfn_h1cIu7BktAtmSYU0K397nJZpYEafNjyUfF0uNgi8B35dfDmWZAafNEdkFIO40PKzotsLzlfouP0PpBC9upD-lNaSoVt1qZNdR3Mck87NEQjIsSicoThk7FjiuiACE1MTQ">
                <span class="font-medium text-sm text-brand-dark"><?php echo htmlspecialchars(getUserName()); ?></span>
                <svg class="text-brand-gray" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
        </div>
    </header>
    <!-- END: Top Navbar -->

    <div class="flex flex-1 overflow-hidden">
        <!-- BEGIN: Side Sidebar -->
        <aside class="w-64 bg-white border-r border-brand-border flex flex-col hidden md:flex">
            <nav class="flex-1 py-6 px-4 space-y-2">
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="dashboard.php">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><rect height="9" width="7" x="3" y="3"></rect><rect height="5" width="7" x="14" y="3"></rect><rect height="9" width="7" x="14" y="12"></rect><rect height="5" width="7" x="3" y="16"></rect></svg>
                    <span class="font-medium text-sm">Dashboard</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="#">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><rect height="14" rx="2" ry="2" width="20" x="2" y="7"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <span class="font-medium text-sm">My Services</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="#">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><rect height="18" rx="2" ry="2" width="18" x="3" y="4"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    <span class="font-medium text-sm">Bookings</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="#">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span class="font-medium text-sm">Reviews</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="#">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><line x1="12" x2="12" y1="1" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span class="font-medium text-sm">Earnings</span>
                </a>
                <!-- Active Tab -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg bg-brand-cream text-brand-terracotta border-l-4 border-brand-terracotta transition-colors" href="#">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span class="font-medium text-sm">Profile</span>
                </a>
            </nav>
            <div class="p-4 border-t border-brand-border space-y-2">
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="#">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    <span class="font-medium text-sm">Settings</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors" href="/local-services-platform/public/logout.php">
                    <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                    <span class="font-medium text-sm">Logout</span>
                </a>
            </div>
        </aside>
        <!-- END: Side Sidebar -->

        <!-- BEGIN: Main Content Area -->
        <main class="flex-1 overflow-y-auto p-8">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-brand-dark flex items-center gap-2">
                        <svg class="text-brand-terracotta" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Edit Profile
                    </h1>
                    <p class="text-brand-gray text-sm mt-1">Update your personal information and contact details.</p>
                </div>
                <a class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-brand-border rounded-lg text-sm font-medium text-brand-dark hover:bg-brand-lightGray transition-colors" href="dashboard.php">
                    <svg fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg"><line x1="19" x2="5" y1="12" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back to Dashboard
                </a>
            </div>

            <!-- Edit Profile Form Card -->
            <div class="bg-white rounded-[1rem] shadow-sm border border-brand-border max-w-3xl">
                <form method="POST" class="p-6 md:p-8 space-y-6">
                    <!-- Error/Success Messages -->
                    <?php if ($error): ?>
                        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                            <p class="font-medium"><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                            <p class="font-medium"><?php echo $success; ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Form Group: Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-brand-dark mb-2" for="name">Full Name *</label>
                        <input class="w-full rounded-lg border border-brand-border shadow-sm focus:border-brand-terracotta focus:ring-2 focus:ring-brand-terracotta px-4 py-2.5 text-brand-dark" id="name" name="name" required type="text" value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>

                    <!-- Form Group: Email (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-brand-dark mb-2" for="email">Email (read-only)</label>
                        <input class="w-full rounded-lg border border-brand-border shadow-sm bg-brand-lightGray text-brand-gray cursor-not-allowed px-4 py-2.5 focus:ring-0 focus:border-brand-border" id="email" name="email" readonly type="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <!-- Form Group: Phone -->
                    <div>
                        <label class="block text-sm font-medium text-brand-dark mb-2" for="phone">Phone</label>
                        <input class="w-full rounded-lg border border-brand-border shadow-sm focus:border-brand-terracotta focus:ring-2 focus:ring-brand-terracotta px-4 py-2.5 text-brand-dark" id="phone" name="phone" type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <!-- Form Group: Address -->
                    <div>
                        <label class="block text-sm font-medium text-brand-dark mb-2" for="address">Address</label>
                        <textarea class="w-full rounded-lg border border-brand-border shadow-sm focus:border-brand-terracotta focus:ring-2 focus:ring-brand-terracotta px-4 py-2.5 text-brand-dark resize-y" id="address" name="address" rows="4"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-4">
                        <button class="px-6 py-2.5 text-brand-gray font-medium hover:text-brand-dark transition-colors" type="button" onclick="window.history.back();">
                            Cancel
                        </button>
                        <button class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-brand-terracotta text-white font-medium rounded-lg hover:bg-[#b05d43] transition-colors focus:ring-4 focus:ring-brand-rose/30" type="submit">
                            <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>
        <!-- END: Main Content Area -->
    </div>
</body>
</html>