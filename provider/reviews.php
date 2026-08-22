<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();

// جلب جميع التقييمات الخاصة بخدمات مقدم الخدمة
$stmt = $pdo->prepare("
    SELECT r.*, u.name as customer_name, s.title as service_title
    FROM reviews r
    JOIN users u ON r.customer_id = u.id
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$provider_id]);
$reviews = $stmt->fetchAll();

// حساب متوسط التقييم
$stmt = $pdo->prepare("
    SELECT COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as total 
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
");
$stmt->execute([$provider_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - Provider</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        .text-terracotta {
            color: #CB6D51;
        }

        .border-terracotta {
            border-color: #CB6D51;
        }
        
        input:focus, textarea:focus {
            --tw-ring-color: #CB6D51 !important;
            border-color: #CB6D51 !important;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen font-sans">
    <?php include __DIR__ . '/header.php'; ?>
    <?php if (false): ?>
    <!-- BEGIN: Top Navbar -->
    <header class="bg-white border-b border-brand-border h-16 flex items-center justify-between px-6 sticky top-0 z-50">
        <div class="flex items-center gap-2 text-brand-terracotta font-bold text-xl">
            <svg fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Dabberha</span>
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
    <?php endif; ?>
    <!-- END: Top Navbar -->

    <div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">
        <!-- BEGIN: Sidebar Navigation -->
        <?php if (false): ?>
        <aside class="w-64 bg-white border-r border-brand-border flex flex-col hidden md:flex z-20">
            <!-- Logo Area -->
            <div class="h-16 flex items-center px-6 border-b border-brand-border">
                <a class="flex items-center gap-2 text-brand-terracotta font-bold text-xl" href="dashboard.php">
                    <svg fill="currentColor" height="24" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path></svg>
                    Dabberha
                </a>
            </div>
            <!-- Provider Profile Summary -->
            <div class="p-6 border-b border-brand-border flex items-center gap-3">
                <img alt="Provider Profile Picture" class="w-10 h-10 rounded-full object-cover border border-brand-border" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRCNnYshlZBIKzFfON145cRr3-9aq6Uyo6e9voA7TaYwd6ZPi3YBoEafn_ea5AdX25hgGti2B28CG9IPwOwFx6_zkKwfYgmcHcyRXKbLceACaxqVyrJIATzhZAjhSenlF-R1o1Gtn1VYeqk3tnPqGEYIyIJEe3ZlBbQ3e_xEvYQjMEDUkVGvlRezVBQFBP2IDX-JqRVFDH6k6-GHY_q0_yyVgDzWc2kwXf_aKu11-tGw35C-v36bsP-w">
                <div>
                    <p class="font-semibold text-sm text-brand-dark"><?php echo htmlspecialchars(getUserName()); ?></p>
                    <p class="text-xs text-brand-gray">Local Expert</p>
                </div>
            </div>
            <!-- Nav Links -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm" href="dashboard.php">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm" href="my-services.php">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m0 0l-8-4m8 4v10l-8-4m8 4l8-4m0 0V7m0 0l-8-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            My Services
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm" href="bookings.php">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            Bookings
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-brand-cream text-brand-terracotta border-l-4 border-brand-terracotta transition-colors font-medium text-sm" href="reviews.php">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-5.82 3.37 1.18-6.88-5-4.87 6.91-1.01L12 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            Reviews
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm" href="#">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            Earnings
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm" href="profile.php">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></circle></svg>
                            Profile
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- Bottom Actions -->
            <div class="p-4 border-t border-brand-border space-y-2">
                <a class="flex items-center justify-center w-full py-2 px-4 bg-brand-terracotta text-white rounded-lg font-medium text-sm hover:bg-[#b05d43] transition-colors" href="add-service.php">
                    New Listing
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm mt-4" href="#">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    Settings
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-brand-gray hover:bg-brand-cream hover:text-brand-terracotta transition-colors font-medium text-sm" href="/local-services-platform/public/logout.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    Logout
                </a>
            </div>
        </aside>
        <?php endif; include __DIR__ . '/sidebar.php'; ?>
        <!-- END: Sidebar Navigation -->

        <!-- BEGIN: Main Content Area -->
        <main class="flex-1 overflow-y-auto p-8">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-brand-dark flex items-center gap-2">
                        <svg class="text-brand-terracotta" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        My Reviews
                    </h1>
                    <p class="text-brand-gray text-sm mt-1">What customers say about your services</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Average Rating Card -->
                <div class="bg-white rounded-[1rem] shadow-sm border border-brand-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-brand-gray text-sm font-medium">Average Rating</p>
                            <h3 class="text-3xl font-bold text-brand-terracotta mt-2">
                                <?php echo number_format($stats['avg_rating'], 1); ?> 
                                <span class="text-xl">⭐</span>
                            </h3>
                        </div>
                        <div class="w-12 h-12 bg-brand-cream rounded-lg flex items-center justify-center">
                            <svg fill="currentColor" height="24" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" class="text-brand-terracotta">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Reviews Card -->
                <div class="bg-white rounded-[1rem] shadow-sm border border-brand-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-brand-gray text-sm font-medium">Total Reviews</p>
                            <h3 class="text-3xl font-bold text-brand-dark mt-2"><?php echo $stats['total']; ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-brand-cream rounded-lg flex items-center justify-center">
                            <svg fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" class="text-brand-terracotta">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div class="bg-white rounded-[1rem] shadow-sm border border-brand-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-brand-gray text-sm font-medium">Status</p>
                            <p class="text-lg font-semibold text-brand-dark mt-2">
                                <?php echo $stats['total'] > 0 ? 'Active' : 'No Reviews Yet'; ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-brand-cream rounded-lg flex items-center justify-center">
                            <svg fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" class="text-brand-terracotta">
                                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 6v6l4 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <?php if (count($reviews) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($reviews as $review): ?>
                        <div class="bg-white rounded-[1rem] shadow-sm border border-brand-border p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-brand-dark">
                                        <?php echo htmlspecialchars($review['customer_name']); ?>
                                    </h4>
                                    <p class="text-sm text-brand-gray">
                                        on <span class="font-medium"><?php echo htmlspecialchars($review['service_title']); ?></span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                        <span class="text-lg">⭐</span>
                                    <?php endfor; ?>
                                    <span class="ml-2 text-sm font-medium text-brand-dark"><?php echo $review['rating']; ?>/5</span>
                                </div>
                            </div>
                            <p class="text-brand-dark mb-4"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <p class="text-xs text-brand-gray">
                                <?php echo date('F d, Y h:i A', strtotime($review['created_at'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-[1rem] shadow-sm border border-brand-border p-12 text-center">
                    <svg class="w-16 h-16 text-brand-gray mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <h3 class="text-lg font-semibold text-brand-dark mb-2">No Reviews Yet</h3>
                    <p class="text-brand-gray">Keep providing great service! Your customers' reviews will appear here.</p>
                </div>
            <?php endif; ?>
        </main>
        <!-- END: Main Content Area -->
    </div>
</body>
</html>
