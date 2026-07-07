<?php
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md relative">
        <a href="dashboard.php" class="absolute top-4 left-4 text-gray-400 hover:text-blue-600">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>

        <div class="text-center mb-6">
            <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-4xl font-bold mx-auto mb-4">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <span class="bg-gray-100 text-gray-500 text-xs uppercase px-3 py-1 rounded-full"><?php echo $user['role']; ?></span>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <p class="text-green-600 text-center text-sm mb-4">Profile updated successfully!</p>
        <?php endif; ?>

        <form action="actions/update_profile.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email (Read Only)</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="w-full p-2 border rounded bg-gray-100 text-gray-500 cursor-not-allowed">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password (Optional)</label>
                <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full p-2 border rounded">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">Update Profile</button>
        </form>
    </div>

</body>
</html>