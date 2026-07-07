<?php require 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">TaskHub Login</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <p class="bg-red-100 text-red-600 p-2 rounded mb-4 text-sm text-center">Invalid Email or Password</p>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
            <p class="bg-green-100 text-green-600 p-2 rounded mb-4 text-sm text-center">Registration Successful! Please Login.</p>
        <?php endif; ?>

        <form action="actions/login_action.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" required class="w-full p-2 border rounded focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full p-2 border rounded focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">Login</button>
        </form>
        <p class="mt-4 text-center text-sm">No account? <a href="register.php" class="text-blue-500 hover:underline">Register here</a></p>
    </div>
</body>
</html>