<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Components</title>
    <script src="assets/js/tailwind.js"></script>
    <script src="assets/js/lucide.js"></script>
    <style>
        .group:hover .group-hover\:block { display: block; }
        .dropdown-menu { margin-top: 0; padding-top: 10px; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans text-gray-800">

<header class="bg-[#131921] text-white sticky top-0 z-50 shadow-md">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between gap-4">
        
        <a href="index.php" class="flex items-center gap-1">
            <div class="bg-yellow-400 text-black p-1 rounded font-bold tracking-tighter">NEXUS</div>
            <span class="font-bold text-xl hidden sm:block">Components</span>
        </a>

        <div class="flex-1 max-w-2xl hidden md:flex">
            <form action="shop.php" method="GET" class="flex w-full rounded-md overflow-hidden bg-white">
                <input type="text" name="q" placeholder="Search products..." class="w-full px-4 py-2 text-gray-900 focus:outline-none">
                <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 px-5 flex items-center justify-center text-gray-900">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </button>
            </form>
        </div>

        <div class="flex items-center gap-6 text-sm relative">
            
            <a href="shop.php" class="hover:text-yellow-400 transition font-bold">Shop</a>
            
            <a href="contact.php" class="hover:text-yellow-400 transition font-bold">Contact</a>

            <div class="relative group h-full flex items-center cursor-pointer">
                <div class="leading-none text-right">
                    <p class="text-[10px] text-gray-300">
                        <?php echo isset($_SESSION['username']) ? 'Hello, ' . htmlspecialchars($_SESSION['username']) : 'Hello, Sign in'; ?>
                    </p>
                    <p class="font-bold">Account & Lists</p>
                </div>
                
                <div class="absolute right-0 top-full w-48 bg-white text-gray-900 rounded shadow-xl hidden group-hover:block z-50 dropdown-menu">
                    <div class="p-2 border border-gray-200 rounded">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['role'] === 'admin'): ?>
                                <a href="admin.php" class="block px-4 py-2 hover:bg-gray-100 rounded">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="auth.php?action=logout" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded">Sign Out</a>
                        <?php else: ?>
                            <div class="text-center p-2">
                                <a href="auth.php?mode=login" class="block bg-yellow-400 py-1 rounded font-bold text-sm mb-2">Sign In</a>
                                <p class="text-xs">New here? <a href="auth.php?mode=signup" class="text-blue-600 hover:underline">Start here.</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <a href="cart.php" class="flex items-end relative hover:text-yellow-400">
                <i data-lucide="shopping-cart" class="w-7 h-7"></i>
                <span class="absolute -top-1 -right-2 bg-yellow-400 text-black font-bold text-xs w-5 h-5 rounded-full flex items-center justify-center">
                    <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                </span>
                <span class="font-bold hidden sm:block ml-1 translate-y-0.5">Cart</span>
            </a>
        </div>
    </div>
</header>
<main class="flex-1 container mx-auto px-4 py-8">