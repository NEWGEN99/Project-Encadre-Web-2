<?php
require_once 'includes/db.php';

/*
|--------------------------------------------------------------------------
| Redirect handling
|--------------------------------------------------------------------------
*/
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'index.php';

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

$mode = $_GET['mode'] ?? 'login';
$error = '';

/*
|--------------------------------------------------------------------------
| Form Handling
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $pass  = trim($_POST['password']);

    /*
    |-------------------
    | Signup
    |-------------------
    */
    if (isset($_POST['signup'])) {

        $name = trim($_POST['username']);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, role)
                 VALUES (?, ?, ?, 'customer')"
            );
            $stmt->execute([$name, $email, $pass]);

            header("Location: auth.php?mode=login&success=1&redirect=" . urlencode($redirect));
            exit;

        } catch (PDOException $e) {
            $error = "Email already exists!";
        }
    }

    /*
    |-------------------
    | Login
    |-------------------
    */
    if (isset($_POST['login'])) {

        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE email = ? AND password = ?"
        );
        $stmt->execute([$email, $pass]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // Admin always goes to admin panel
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: " . $redirect);
            }
            exit;

        } else {
            $error = "Wrong email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus | Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-lg shadow-lg w-[400px]">

    <div class="flex justify-center mb-6">
        <a href="index.php" class="text-2xl font-bold italic">NEXUS</a>
    </div>

    <div class="flex border-b mb-6 text-center">
        <a href="?mode=login&redirect=<?php echo urlencode($redirect); ?>"
           class="flex-1 pb-2 <?php echo $mode === 'login' ? 'border-b-2 border-yellow-500 font-bold' : 'text-gray-400'; ?>">
            Login
        </a>
        <a href="?mode=signup&redirect=<?php echo urlencode($redirect); ?>"
           class="flex-1 pb-2 <?php echo $mode === 'signup' ? 'border-b-2 border-yellow-500 font-bold' : 'text-gray-400'; ?>">
            Register
        </a>
    </div>

    <?php if ($error): ?>
        <div class="text-red-500 text-sm mb-4 text-center">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">

        <!-- Redirect -->
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

        <?php if ($mode === 'signup'): ?>
            <input type="text" name="username" placeholder="Full Name"
                   required class="w-full border p-2 rounded">
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email"
               required class="w-full border p-2 rounded">

        <input type="password" name="password" placeholder="Password"
               required class="w-full border p-2 rounded">

        <button type="submit"
                name="<?php echo $mode; ?>"
                class="w-full bg-yellow-400 py-2 rounded font-bold hover:bg-yellow-500 transition">
            <?php echo $mode === 'login' ? 'Sign In' : 'Create Account'; ?>
        </button>

    </form>
</div>

</body>
</html>
