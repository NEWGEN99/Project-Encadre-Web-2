<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'nexus_db';
$user = 'root';
$pass = 'usbw'; // غيرها حسب إعداداتك

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Error. Please create the database first.");
}

// دالة مساعدة لرفع الصور
function uploadImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        
        // التحقق من نوع الملف
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if(in_array($imageFileType, ['jpg', 'png', 'jpeg', 'webp'])) {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return $target_file;
            }
        }
    }
    return null;
}
?>