<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// 1. جلب بيانات المنتج
// -------------------------------------------------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// نربط جدول المنتجات مع الفئات لجلب اسم الفئة
$stmt = $pdo->prepare("
    SELECT p.*, c.name as cat_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// إذا لم يوجد المنتج، نوقف الصفحة
if(!$product) {
    echo "<div class='container mx-auto py-20 text-center text-xl font-bold'>Product not found. <a href='shop.php' class='text-blue-600 underline'>Go Back</a></div>";
    require_once 'includes/footer.php';
    exit;
}

// 2. معالجة إضافة مراجعة (Review)
// -------------------------------------------------------
if(isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $id, 
        $_SESSION['user_id'], 
        $_POST['rating'], 
        $_POST['comment']
    ]);
    // إعادة تحميل الصفحة لمنع تكرار الإرسال
    header("Location: product.php?id=$id");
    exit;
}

// 3. جلب المراجعات الخاصة بالمنتج
// -------------------------------------------------------
$reviews_stmt = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE product_id = ? 
    ORDER BY created_at DESC
");
$reviews_stmt->execute([$id]);
$reviews_count = $reviews_stmt->rowCount();

// تجميع الصور في مصفوفة واحدة لتسهيل العرض
$gallery_images = [
    $product['image_main'],
    $product['image_2'],
    $product['image_3'],
    $product['image_4']
];
// حذف الخانات الفارغة (إذا لم ترفع 4 صور)
$gallery_images = array_filter($gallery_images);
?>

<div class="bg-white min-h-screen pb-10">
    <div class="container mx-auto px-4 py-8">
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-5/12">
                <div class="flex flex-col-reverse md:flex-row gap-4 h-full">
                    
                    <div class="flex md:flex-col gap-2 overflow-x-auto md:overflow-visible justify-center md:justify-start">
                        <?php foreach($gallery_images as $img): ?>
                        <div class="w-16 h-16 border-2 border-gray-200 hover:border-yellow-400 rounded p-1 cursor-pointer bg-white"
                             onmouseover="changeImage('<?php echo $img; ?>')"
                             onclick="changeImage('<?php echo $img; ?>')">
                            <img src="<?php echo $img; ?>" class="w-full h-full object-contain">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex-1 border rounded-lg bg-white flex items-center justify-center p-4 relative h-[400px] md:h-[500px]">
                        <img id="mainImage" src="<?php echo $product['image_main']; ?>" class="max-w-full max-h-full object-contain">
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-4/12">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="text-sm text-gray-500 mb-2">
                    Category: <a href="shop.php?category=<?php echo $product['category_id']; ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($product['cat_name']); ?></a>
                </div>

                <div class="flex items-center gap-2 mb-4 border-b pb-4">
                    <div class="flex text-yellow-400 text-sm">
                        <?php for($i=0; $i<5; $i++) echo '<i data-lucide="star" class="w-4 h-4 fill-current"></i>'; ?>
                    </div>
                    <a href="#reviews-section" class="text-blue-600 text-sm hover:underline"><?php echo $reviews_count; ?> ratings</a>
                </div>

                <div class="mb-4">
                    <span class="text-sm text-gray-500">Price:</span>
                    <span class="text-3xl font-bold text-red-700 block">$<?php echo number_format($product['price'], 2); ?></span>
                    <span class="text-sm text-gray-500">All prices include VAT.</span>
                </div>

                <div class="mb-6">
                    <h3 class="font-bold mb-2">About this item</h3>
                    <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
                        <?php 
                        // تحويل الأسطر الجديدة إلى عناصر قائمة للتنسيق
                        $short_desc_lines = explode("\n", $product['short_description']);
                        foreach($short_desc_lines as $line) {
                            if(trim($line)) echo "<li>" . htmlspecialchars($line) . "</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="w-full lg:w-3/12">
                <div class="border rounded-lg p-5 shadow-sm bg-gray-50 sticky top-24">
                    <div class="text-2xl font-bold text-red-700 mb-2">$<?php echo number_format($product['price'], 2); ?></div>
                    
                    <?php if($product['stock'] > 0): ?>
                        <div class="text-green-600 font-bold text-lg mb-4">In Stock.</div>
                        
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-4">
                                <label class="block text-xs font-bold mb-1">Quantity:</label>
                                <select name="qty" class="w-full border border-gray-300 rounded p-2 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                    <?php 
                                    // الحد الأقصى للكمية المسموح بها في الطلب الواحد (مثلاً 10 أو حسب المخزون)
                                    $max_qty = min(10, $product['stock']);
                                    for($i=1; $i <= $max_qty; $i++): 
                                    ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 py-2 rounded-full font-bold shadow-md transition transform active:scale-95">
                                Add to Cart
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-red-600 font-bold text-lg mb-4">Currently Unavailable.</div>
                        <p class="text-sm text-gray-500">We don't know when or if this item will be back in stock.</p>
                    <?php endif; ?>

                    <div class="text-xs text-gray-500 mt-4 space-y-1">
                        <div class="flex justify-between"><span>Ships from</span> <strong>Nexus</strong></div>
                        <div class="flex justify-between"><span>Sold by</span> <strong>Nexus</strong></div>
                        <div class="flex justify-between"><span>Payment</span> <strong>Secure transaction</strong></div>
                    </div>
                </div>
            </div>

        </div>

        <hr class="my-10 border-gray-200">

        <div class="max-w-4xl">
            <h2 class="text-2xl font-bold text-orange-600 mb-4">Product Description</h2>
            <div class="prose max-w-none text-gray-800 leading-relaxed bg-gray-50 p-6 rounded border">
                <?php echo nl2br(htmlspecialchars($product['full_description'])); ?>
            </div>
        </div>

        <hr class="my-10 border-gray-200">

        <div id="reviews-section" class="max-w-4xl">
            <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
            
            <div class="flex flex-col md:flex-row gap-10">
                <div class="md:w-1/3">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="bg-gray-50 p-5 rounded border">
                            <h3 class="font-bold mb-3">Write a review</h3>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="block text-xs font-bold mb-1">Rating</label>
                                    <select name="rating" class="w-full border p-2 rounded">
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Good</option>
                                        <option value="3">3 Stars - Average</option>
                                        <option value="2">2 Stars - Poor</option>
                                        <option value="1">1 Star - Terrible</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-xs font-bold mb-1">Comment</label>
                                    <textarea name="comment" rows="4" class="w-full border p-2 rounded" placeholder="What did you like or dislike?" required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-black w-full">Submit Review</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 p-5 rounded border text-center">
                            <p class="mb-3">Please sign in to write a review.</p>
                            <a href="auth.php?mode=login&redirect=product.php?id=<?php echo $id; ?>" class="bg-yellow-400 px-4 py-2 rounded font-bold text-sm block">Sign In</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="md:w-2/3 space-y-6">
                    <?php if($reviews_count > 0): ?>
                        <?php while($rev = $reviews_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="border-b pb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">
                                    <?php echo strtoupper(substr($rev['username'], 0, 1)); ?>
                                </div>
                                <span class="font-bold text-sm"><?php echo htmlspecialchars($rev['username']); ?></span>
                            </div>
                            <div class="flex text-yellow-500 text-xs mb-2">
                                <?php 
                                for($i=0; $i<$rev['rating']; $i++) echo '★';
                                for($i=$rev['rating']; $i<5; $i++) echo '☆'; 
                                ?>
                                <span class="text-gray-400 ml-2 font-normal text-[10px] pt-0.5"><?php echo $rev['created_at']; ?></span>
                            </div>
                            <p class="text-gray-700 text-sm"><?php echo htmlspecialchars($rev['comment']); ?></p>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 italic">No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function changeImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>

<?php require_once 'includes/footer.php'; ?>