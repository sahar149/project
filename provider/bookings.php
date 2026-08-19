<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();

// جلب جميع الحجوزات
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, u.name as customer_name 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE b.provider_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$provider_id]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Provider Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script data-purpose="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            primary: '#CB6D51',
                            secondary: '#C18B8B',
                            surface: '#fff8f6',
                            dark: '#3A2F2B',
                            muted: '#7a6e69',
                            border: '#e9d6d0',
                        }
                    },
                    borderRadius: {
                        'xl': '0.75rem',
                    }
                }
            }
        }
    </script>
    <style data-purpose="custom-utilities">
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #e9d6d0;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #c18b8b;
        }
    </style>
</head>
<body class="font-sans bg-brand-surface text-brand-dark antialiased h-screen flex overflow-hidden">
    <!-- BEGIN: Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-brand-border flex flex-col hidden md:flex z-20">
        <!-- Logo Area -->
        <div class="h-16 flex items-center px-6 border-b border-brand-border">
            <a class="flex items-center gap-2 text-brand-primary font-bold text-xl" href="dashboard.php">
                <svg class="w-6 h-6" fill="currentColor" viewbox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path></svg>
                LocalEase
            </a>
        </div>
        <!-- Provider Profile Summary -->
        <div class="p-6 border-b border-brand-border flex items-center gap-3">
            <img alt="<?php echo htmlspecialchars(getUserName()); ?>" class="w-10 h-10 rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRCNnYshlZBIKzFfON145cRr3-9aq6Uyo6e9voA7TaYwd6ZPi3YBoEafn_ea5AdX25hgGti2B28CG9IPwOwFx6_zkKwfYgmcHcyRXKbLceACaxqVyrJIATzhZAjhSenlF-R1o1Gtn1VYeqk3tnPqGEYIyIJEe3ZlBbQ3e_xEvYQjMEDUkVGvlRezVBQFBP2IDX-JqRVFDH6k6-GHY_q0_yyVgDzWc2kwXf_aKu11-tGw35C-v36bsP-w">
            <div>
                <p class="font-semibold text-sm"><?php echo htmlspecialchars(getUserName()); ?></p>
                <p class="text-xs text-brand-muted">Local Expert</p>
            </div>
        </div>
        <!-- Nav Links -->
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-muted hover:bg-brand-surface hover:text-brand-dark transition-colors font-medium text-sm" href="dashboard.php">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-brand-primary/10 text-brand-primary font-medium text-sm" href="#">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        Bookings
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-muted hover:bg-brand-surface hover:text-brand-dark transition-colors font-medium text-sm" href="#">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        Earnings
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-muted hover:bg-brand-surface hover:text-brand-dark transition-colors font-medium text-sm" href="#">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        Clients
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Bottom Actions -->
        <div class="p-4 border-t border-brand-border space-y-2">
            <a class="flex items-center justify-center w-full py-2 px-4 bg-brand-primary text-white rounded-lg font-medium text-sm hover:bg-[#b05b41] transition-colors" href="add-service.php">
                New Listing
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-brand-muted hover:bg-brand-surface hover:text-brand-dark transition-colors font-medium text-sm mt-4" href="#">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                Settings
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-brand-muted hover:bg-brand-surface hover:text-brand-dark transition-colors font-medium text-sm" href="/local-services-platform/public/logout.php">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                Logout
            </a>
        </div>
    </aside>
    <!-- END: Sidebar Navigation -->

    <!-- BEGIN: Main Layout -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- BEGIN: Top Navigation -->
        <header class="h-16 bg-white border-b border-brand-border flex items-center justify-between px-8 z-10 shrink-0">
            <div class="flex gap-6 text-sm font-medium text-brand-muted">
                <a class="hover:text-brand-dark transition-colors" href="#">Find Services</a>
                <a class="hover:text-brand-dark transition-colors" href="#">How it Works</a>
                <a class="hover:text-brand-dark transition-colors" href="#">Community</a>
            </div>
            <div class="flex items-center gap-5">
                <button class="text-brand-muted hover:text-brand-dark relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-brand-primary rounded-full"></span>
                </button>
                <button class="text-brand-muted hover:text-brand-dark">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                </button>
                <button class="flex items-center gap-2">
                    <img alt="Profile" class="w-8 h-8 rounded-full border border-brand-border object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMGhlnwgi8L6GQdQq-gwaDKVBBapQocrqbJefpboGpYJhSrE8v64jTA5DZ_UYvaVdt5aw67MwprOG6WYzXXe8ryWekobgUt0YljvkzBoVyMoafRInvYvccJVi3dOym6M7FmO9ksitRuVI79sP_L5RzakWa9SCIrZIED-MS3WcENOnd9xhkJK3IaY9jj3UnhNhkj6MUoXNLWCze1BOGz1u2DBpr7Fr8NYBpPHKiVKu9j4z-HuLcGO_nHw">
                </button>
            </div>
        </header>
        <!-- END: Top Navigation -->

        <!-- BEGIN: Main Content Area -->
        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold flex items-center gap-3">
                            <svg class="w-8 h-8 text-brand-primary" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            My Bookings
                        </h1>
                        <p class="text-brand-muted mt-1">All booking requests for your services</p>
                    </div>
                    <a class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-brand-border rounded-lg text-sm font-medium hover:bg-brand-surface hover:border-brand-primary transition-colors text-brand-dark" href="dashboard.php">
                        <svg class="w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        Back to Dashboard
                    </a>
                </div>

                <!-- Bookings Table Card -->
                <?php if (count($bookings) > 0): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-brand-border overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-brand-border bg-brand-surface/50 text-xs uppercase tracking-wider text-brand-muted">
                                        <th class="px-6 py-4 font-semibold">#</th>
                                        <th class="px-6 py-4 font-semibold">Customer</th>
                                        <th class="px-6 py-4 font-semibold">Service</th>
                                        <th class="px-6 py-4 font-semibold">Date</th>
                                        <th class="px-6 py-4 font-semibold">Price</th>
                                        <th class="px-6 py-4 font-semibold">Status</th>
                                        <th class="px-6 py-4 font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-border text-sm">
                                    <?php foreach ($bookings as $index => $booking): 
                                        $status_colors = [
                                            'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-200'],
                                            'confirmed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
                                            'completed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200'],
                                            'cancelled' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'border' => 'border-rose-200']
                                        ];
                                        $colors = $status_colors[$booking['status']] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200'];
                                    ?>
                                        <tr class="hover:bg-brand-surface/30 transition-colors">
                                            <td class="px-6 py-4 text-brand-muted"><?php echo $index + 1; ?></td>
                                            <td class="px-6 py-4 font-medium text-brand-dark"><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                            <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td class="px-6 py-4 font-medium">$<?php echo number_format($booking['total_price'], 2); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $colors['bg'] . ' ' . $colors['text'] . ' border ' . $colors['border']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-brand-primary text-brand-primary rounded-lg text-xs font-medium hover:bg-brand-primary hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-brand-primary">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Table Footer -->
                        <div class="px-6 py-4 border-t border-brand-border bg-brand-surface/30 flex items-center justify-between">
                            <p class="text-sm text-brand-muted">
                                Showing <span class="font-medium text-brand-dark"><?php echo min(count($bookings), 6); ?></span> 
                                to <span class="font-medium text-brand-dark"><?php echo count($bookings); ?></span> 
                                of <span class="font-medium text-brand-dark"><?php echo count($bookings); ?></span> results
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="bg-white rounded-xl shadow-sm border border-brand-border p-12 text-center">
                        <svg class="w-16 h-16 text-brand-muted mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path></svg>
                        <h3 class="text-lg font-semibold text-brand-dark mb-2">No Bookings Yet</h3>
                        <p class="text-brand-muted mb-6">You don't have any booking requests. When customers book your services, they will appear here.</p>
                        <a href="my-services.php" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-primary hover:bg-[#b05b41] text-white rounded-lg font-medium transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            View My Services
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        <!-- END: Main Content Area -->
    </div>
    <!-- END: Main Layout -->
</body>
</html>