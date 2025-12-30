<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$sql = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if (!empty($_GET['q'])) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%" . $_GET['q'] . "%";
}

if (!empty($_GET['category'])) {
    $sql .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
}

if (!empty($_GET['price_range'])) {
    $range = explode('-', $_GET['price_range']);
    if(count($range) == 2) {
        $sql .= " AND p.price BETWEEN ? AND ?";
        $params[] = $range[0];
        $params[] = $range[1];
    } elseif ($_GET['price_range'] == '200+') {
        $sql .= " AND p.price >= 200";
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="flex flex-col md:flex-row gap-6 mt-4">
    <aside class="w-full md:w-64 flex-shrink-0 bg-white p-4 border rounded-lg h-fit">
        <h3 class="font-bold border-b pb-2 mb-4">Filters</h3>
        <form method="GET">
            <?php if(!empty($_GET['q'])): ?><input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>"><?php endif; ?>
            
            <div class="mb-6">
                <p class="font-semibold text-sm mb-2">Category</p>
                <div class="space-y-1 text-sm text-gray-600">
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                        <input type="radio" name="category" value="" <?= empty($_GET['category']) ? 'checked' : '' ?> onchange="this.form.submit()"> All
                    </label>
                    <?php foreach($cats as $c): ?>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                        <input type="radio" name="category" value="<?= $c['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'checked' : '' ?> onchange="this.form.submit()"> 
                        <?= $c['name'] ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-6">
                <p class="font-semibold text-sm mb-2">Price</p>
                <div class="space-y-1 text-sm text-gray-600">
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price_range" value="0-50" onchange="this.form.submit()"> Under $50</label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price_range" value="50-200" onchange="this.form.submit()"> $50 to $200</label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price_range" value="200+" onchange="this.form.submit()"> Over $200</label>
                </div>
            </div>
            
            <a href="shop.php" class="text-xs text-blue-600 underline">Clear All Filters</a>
        </form>
    </aside>

    <div class="flex-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach($products as $row): ?>
            <div class="bg-white border hover:shadow-lg hover:-translate-y-1 transition duration-300 p-3 rounded flex flex-col h-full group">
                <a href="product.php?id=<?= $row['id']; ?>" class="h-48 flex items-center justify-center mb-3 bg-gray-50 rounded overflow-hidden">
                    <img src="<?= $row['image_main']; ?>" class="max-h-full max-w-full object-contain mix-blend-multiply group-hover:scale-105 transition">
                </a>
                
                <div class="flex-1 flex flex-col">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-1"><?= htmlspecialchars($row['cat_name'] ?? 'Hardware'); ?></div>
                    <a href="product.php?id=<?= $row['id']; ?>" class="text-sm font-medium text-gray-900 hover:text-orange-600 line-clamp-1 mb-1">
                        <?= $row['name']; ?>
                    </a>
                    
                    <p class="text-xs text-gray-500 line-clamp-2 mb-2">
                        <?= htmlspecialchars(substr($row['full_description'], 0, 100)) ?>...
                    </p>

                    <div class="mt-auto">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xl font-bold">$<?= number_format($row['price'], 2); ?></p>
                            <div class="text-yellow-400 text-xs flex">
                                ★★★★☆
                            </div>
                        </div>

                        <?php if($row['stock'] > 0): ?>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?= $row['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="w-full bg-yellow-400 text-sm font-bold py-2 rounded-full hover:bg-yellow-500 transition flex items-center justify-center gap-2">
                                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="w-full bg-gray-200 text-gray-500 text-sm font-bold py-2 rounded-full cursor-not-allowed">
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if(empty($products)): ?>
            <div class="text-center py-20 text-gray-500">No products found matching your criteria.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>