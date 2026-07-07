<?php
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Fetch existing tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tags</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800">Manage Tags</h2>
            <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 font-medium">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        <form action="actions/manage_tags_action.php" method="POST" class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <input type="hidden" name="action" value="add">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tag Name</label>
                    <input type="text" name="tag_name" placeholder="e.g. Critical Bug" required class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Color Theme</label>
                    <select name="tag_color" class="w-full border p-2 rounded focus:outline-none cursor-pointer">
                        <option value="red">Red</option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="yellow">Yellow</option>
                        <option value="purple">Purple</option>
                        <option value="pink">Pink</option>
                        <option value="indigo">Indigo</option>
                        <option value="gray">Gray</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Add Tag
            </button>
        </form>

        <h3 class="text-lg font-bold text-gray-700 mb-3">Existing Tags</h3>
        <?php if(count($tags) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <?php foreach($tags as $t): ?>
                    <div class="flex justify-between items-center p-2 rounded border bg-white shadow-sm">
                        <span class="text-xs px-2 py-1 rounded border font-medium <?php echo $t['color_class']; ?>">
                            <?php echo htmlspecialchars($t['name']); ?>
                        </span>
                        <a href="actions/manage_tags_action.php?action=delete&id=<?php echo $t['id']; ?>" 
                           onclick="return confirm('Delete this tag?')" 
                           class="text-gray-400 hover:text-red-500">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-400 text-sm italic">No tags created yet.</p>
        <?php endif; ?>
    </div>

</body>
</html>