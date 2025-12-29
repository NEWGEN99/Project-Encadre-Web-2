<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// جلب آخر 4 منتجات (الأحدث)
$stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 4");
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="relative bg-gray-900 text-white rounded-3xl overflow-hidden mb-16 shadow-2xl h-[400px] flex items-center">
    <div class="absolute inset-0 bg-gradient-to-r from-black to-transparent z-10"></div>
    <div class="container mx-auto px-10 relative z-20">
        <h1 class="text-5xl font-bold mb-4">Upgrade Your Setup</h1>
        <p class="text-xl mb-6 text-gray-300">The best components for high performance gaming.</p>
        <a href="shop.php" class="bg-yellow-400 text-black px-8 py-3 rounded-full font-bold hover:bg-yellow-500 transition">Shop Now</a>
    </div>
    <img src="https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&w=1600&q=80" class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-overlay">
</div>

<div class="mb-16">
    <h2 class="text-3xl font-bold text-gray-900 mb-8 border-l-4 border-yellow-400 pl-4">New Arrivals</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach($featured as $product): ?>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full group">
                <div class="relative aspect-[4/3] bg-gray-50 p-4 flex items-center justify-center">
                    <img src="<?php echo htmlspecialchars($product['image_main']); ?>" 
                         class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500">
                    <span class="absolute top-3 left-3 bg-yellow-400 text-xs font-bold px-2 py-1 rounded">NEW</span>
                </div>
                
                <div class="p-5 flex flex-col flex-1">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-1"><?php echo htmlspecialchars($product['cat_name'] ?? 'Hardware'); ?></div>
                    <h3 class="font-bold text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">$<?php echo number_format($product['price'], 2); ?></span>
                        
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="bg-gray-900 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-yellow-400 hover:text-black transition">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>