<?php
session_start();

if (isset($_SESSION['loggedin'])) {
    header("Location: home.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = 'admin';
        header("Location: home.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trustmedis Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#003366] flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-10">
            <img src="assets/download.png" alt="Logo" class="w-60 mx-auto mb-4">
        </div>

        <h2 class="text-xl text-center font-semibold text-gray-700 mb-6">Task Monitoring System</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" required 
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00D285] focus:outline-none transition" 
                        placeholder="Masukkan username">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" required 
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00D285] focus:outline-none transition" 
                        placeholder="Masukkan password">
                </div>
            </div>

            <button type="submit" 
                class="w-full bg-[#00D285] text-white font-bold py-3 rounded-lg hover:bg-[#00b572] transition duration-300 shadow-lg">
                MASUK
            </button>
        </form>

        <p class="text-center text-gray-400 text-xs mt-8">
            &copy; 2026 Trustmedis Monitoring System
        </p>
    </div>

</body>
</html>