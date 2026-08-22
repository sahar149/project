<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();

// جلب خدمات مقدم الخدمة
$stmt = $pdo->prepare("
    SELECT s.*, c.name as category_name 
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.provider_id = ?
    ORDER BY s.id DESC
");
$stmt->execute([$provider_id]);
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services - Provider Dashboard</title>
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
                            bg: '#F9F5F1',
                            primary: '#CB6D51',
                            secondary: '#C18B8B',
                            text: '#3A2F2B',
                            card: '#FFFFFF',
                            border: '#E8E1DA',
                            muted: '#7A6D67'
                        }
                    }
                }
            }
        }
    </script>
    <style data-purpose="custom-styles">
        body {
            background-color: #F9F5F1;
            color: #3A2F2B;
        }

        .sidenav-active {
            background-color: #F9F5F1;
            color: #CB6D51;
            border-left: 4px solid #CB6D51;
            font-weight: 600;
        }

        .text-terracotta {
            color: #CB6D51;
        }

        .border-terracotta {
            border-color: #CB6D51;
        }

        .category-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen font-sans antialiased">
    <?php include __DIR__ . '/header.php'; ?>
    <?php if (false): ?>
    <!-- BEGIN: TopNavBar -->
    <header class="bg-brand-card shadow-sm w-full sticky top-0 z-50">
        <div class="flex justify-between items-center w-full px-6 h-16 max-w-[1400px] mx-auto border-b border-brand-border">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-house text-brand-primary text-xl"></i>
                <span class="font-bold text-xl text-brand-primary">Dabberha</span>
            </div>
            <!-- Center Links -->
            <nav class="hidden md:flex gap-6">
                <a class="text-brand-muted hover:text-brand-primary transition-colors text-sm font-medium" href="#">Find Services</a>
                <a class="text-brand-muted hover:text-brand-primary transition-colors text-sm font-medium" href="#">How it Works</a>
                <a class="text-brand-muted hover:text-brand-primary transition-colors text-sm font-medium" href="#">Community</a>
            </nav>
            <!-- Right Actions -->
            <div class="flex items-center gap-4">
                <button class="text-brand-muted hover:text-brand-primary transition-colors">
                    <i class="fa-regular fa-bell text-lg relative">
                        <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-primary"></span>
                        </span>
                    </i>
                </button>
                <button class="text-brand-muted hover:text-brand-primary transition-colors">
                    <i class="fa-regular fa-comment-dots text-lg"></i>
                </button>
                <div class="flex items-center gap-2 border-l border-brand-border pl-4 cursor-pointer">
                    <img alt="<?php echo htmlspecialchars(getUserName()); ?>" class="w-8 h-8 rounded-full object-cover border border-brand-border" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBwA99lEK9iQzmrGmA5Ww_VZFQfEi3bqaBVNHKl2OnNxskcnQapl0fYFJUKJ5BjeA6aWxN3oLTa9eFlMgvRvrcQPx3b0cUMsdtQ72MgDh5Fv74Ukz0_xXpn0DDmgkoJR6-BCgnUeYSMuaBvNcR7Ir_J5luRD6uuIa6OD6jkihxKuhG8FsV6G-U7-3kOxG1_nxSRs_jXdpMro79wonBuDo6Fcrn0iWYiN7jH15Z9I8KwEF9lC6tRB57q4KkWkPtcj7kDB5w">
                    <span class="text-sm font-medium hidden sm:block"><?php echo htmlspecialchars(getUserName()); ?></span>
                    <i class="fa-solid fa-chevron-down text-xs text-brand-muted"></i>
                </div>
            </div>
        </div>
    </header>
    <?php endif; ?>
    <!-- END: TopNavBar -->

    <div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">
        <!-- BEGIN: SideNavBar -->
        <?php if (false): ?>
        <aside class="w-64 flex-shrink-0 bg-brand-card border-r border-brand-border h-[calc(100vh-4rem)] sticky top-16 hidden lg:flex flex-col justify-between py-6">
            <div class="px-6 mb-8 flex items-center gap-3">
                <img alt="Provider Profile Picture" class="w-12 h-12 rounded-full object-cover border border-brand-border" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDIFNLAIZnAlDX-xeChBmikwa_Cocj7iNOSYU-t-WiawIbKSjRw1EnD5oe9R6T28HSfUmTMxJjPPXL0V-QIC4R1YJMNUGidPjMs8GRwY1uI7MJ5bgxpCzBA1vWm3z61VmdjInHFWkfLFadOe4triOYNRWWHmgUIXVvXW1sflKsymA_k85gT0Kaz9kjkLw-r80rscZVM_PhzWqt9iZEU6SRcW7lJGNLvh49OhQ_CQI7iE7mNNudvjZbHxp_AjTDwfijaH_U">
                <div>
                    <h2 class="font-bold text-sm text-brand-text"><?php echo htmlspecialchars(getUserName()); ?></h2>
                    <p class="text-xs text-brand-muted">Local Expert</p>
                </div>
            </div>
            <nav class="flex-1 flex flex-col gap-1 px-4">
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-muted hover:bg-brand-bg transition-colors text-sm font-medium" href="dashboard.php">
                    <i class="fa-solid fa-border-all w-5"></i> Dashboard
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg sidenav-active text-sm font-medium" href="#">
                    <i class="fa-solid fa-briefcase w-5"></i> My Services
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-muted hover:bg-brand-bg transition-colors text-sm font-medium" href="#">
                    <i class="fa-regular fa-calendar-check w-5"></i> Bookings
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-muted hover:bg-brand-bg transition-colors text-sm font-medium" href="#">
                    <i class="fa-solid fa-sack-dollar w-5"></i> Earnings
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-brand-muted hover:bg-brand-bg transition-colors text-sm font-medium" href="#">
                    <i class="fa-solid fa-users w-5"></i> Clients
                </a>
            </nav>
            <div class="px-4 mt-auto space-y-1">
                <a href="add-service.php" class="w-full bg-brand-primary hover:bg-[#b56046] text-white py-2.5 rounded-lg font-medium transition-colors mb-4 text-sm shadow-sm inline-block text-center">
                    New Listing
                </a>
                <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-brand-muted hover:bg-brand-bg transition-colors text-sm font-medium" href="#">
                    <i class="fa-solid fa-gear w-5"></i> Settings
                </a>
                <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-brand-muted hover:bg-brand-bg transition-colors text-sm font-medium" href="/local-services-platform/public/logout.php">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5"></i> Logout
                </a>
            </div>
        </aside>
        <?php endif; include __DIR__ . '/sidebar.php'; ?>
        <!-- END: SideNavBar -->

        <!-- BEGIN: Main Content Area -->
        <main class="flex-1 p-6 lg:p-10 bg-brand-bg overflow-y-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-brand-text flex items-center gap-3">
                            <i class="fa-solid fa-briefcase text-brand-primary"></i> My Services
                        </h1>
                        <p class="text-brand-muted mt-2 text-sm">Manage your services</p>
                    </div>
                    <a href="add-service.php" class="bg-brand-primary hover:bg-[#b56046] text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-plus"></i> Add New Service
                    </a>
                </div>
            </div>

            <!-- Services Grid -->
            <?php if (count($services) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($services as $service): ?>
                        <article class="bg-brand-card rounded-xl p-6 shadow-sm border border-brand-border flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <span class="category-badge bg-blue-50 text-blue-700">
                                        <?php echo htmlspecialchars($service['category_name']); ?>
                                    </span>
                                </div>
                                <h2 class="text-xl font-bold text-brand-text mb-2">
                                    <?php echo htmlspecialchars($service['title']); ?>
                                </h2>
                                <p class="text-brand-muted text-sm line-clamp-2 mb-6">
                                    <?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="flex items-end gap-1 mb-6">
                                    <span class="text-2xl font-bold text-brand-text">
                                        $<?php echo number_format($service['price'], 2); ?>
                                    </span>
                                    <span class="text-brand-muted text-sm mb-1">
                                        / <?php echo htmlspecialchars($service['price_type']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 border-t border-brand-border pt-4">
                                <a href="edit-service.php?id=<?php echo $service['id']; ?>" class="flex-1 flex items-center justify-center gap-2 bg-brand-bg hover:bg-[#f0e8e1] text-brand-text border border-brand-border py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fa-solid fa-pen text-xs"></i> Edit
                                </a>
                                <a href="delete-service.php?id=<?php echo $service['id']; ?>" onclick="return confirm('Are you sure you want to delete this service?');" class="flex-1 flex items-center justify-center gap-2 text-[#D32F2F] hover:bg-[#FDEDED] border border-[#F4C7C7] py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fa-solid fa-trash-can text-xs"></i> Delete
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-brand-card rounded-xl p-12 shadow-sm border border-brand-border text-center mb-8">
                    <i class="fa-regular fa-folder-open text-4xl text-brand-muted mb-4 inline-block opacity-50"></i>
                    <h3 class="text-lg font-semibold text-brand-text mb-2">No Services Added Yet</h3>
                    <p class="text-brand-muted mb-6">Start by adding your first service to get bookings from customers.</p>
                    <a href="add-service.php" class="inline-flex items-center gap-2 bg-brand-primary hover:bg-[#b56046] text-white px-6 py-2.5 rounded-lg font-medium transition-colors text-sm">
                        <i class="fa-solid fa-plus"></i> Add Your First Service
                    </a>
                </div>
            <?php endif; ?>

        </main>
        <!-- END: Main Content Area -->
    </div>
</body>
</html>