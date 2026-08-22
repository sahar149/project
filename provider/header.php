<?php
$header_unread_count = isset($unread_count) ? (int) $unread_count : 0;
$header_provider_name = getUserName();
?>
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a class="flex items-center gap-2 text-2xl font-bold text-terracotta" href="/local-services-platform/index.php">
                    <i class="fa-solid fa-house-chimney"></i>
                    Dabberha
                </a>
            </div>
            <div class="flex items-center gap-4">
                <a href="dashboard.php#notifications" class="text-gray-500 hover:text-terracotta relative transition-colors" aria-label="View notifications">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <?php if ($header_unread_count > 0): ?>
                        <span class="absolute -top-1 -right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                    <?php endif; ?>
                </a>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-orange-50 text-terracotta flex items-center justify-center">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <span class="font-medium text-sm text-gray-700 hidden sm:block">
                        <?php echo htmlspecialchars($header_provider_name); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</header>