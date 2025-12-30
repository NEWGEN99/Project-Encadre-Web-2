<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'nexus_db';
$user = 'root';
$pass = 'usbw';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// دالة مساعدة لرفع الصور داخل مجلدات منظمة
// uploads/CategoryName/ProductID/image.jpg
function uploadImage($file, $categoryName, $productId) {
    if(isset($file) && $file['error'] == 0) {
        // تنظيف اسم الفئة ليكون صالحاً كاسم مجلد
        $safeCatName = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $categoryName));
        
        $target_dir = "uploads/" . $safeCatName . "/" . $productId . "/";
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }
        
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if(in_array($imageFileType, ['jpg', 'png', 'jpeg', 'webp'])) {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return $target_file;
            }
        }
    }
    return null;
}

// دالة لحذف مجلد المنتج بالكامل عند الحذف
function deleteProductFolder($categoryName, $productId) {
    $safeCatName = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $categoryName));
    $dir = "uploads/" . $safeCatName . "/" . $productId;
    
    if (is_dir($dir)) {
        $files = glob($dir . '/*'); 
        foreach($files as $file){ 
            if(is_file($file)) unlink($file); 
        }
        rmdir($dir);
    }
}
?>