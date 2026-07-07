<?php
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'lead')) {
    header("Location: dashboard.php");
    exit();
}

$task_id = $_GET['id'];

// Data Fetching
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?"); $stmt->execute([$task_id]); $task = $stmt->fetch();
if (!$task) { die("Task not found"); }

$stmt_assignees = $pdo->prepare("SELECT user_id FROM task_assignments WHERE task_id = ?"); $stmt_assignees->execute([$task_id]); $current_assignees = $stmt_assignees->fetchAll(PDO::FETCH_COLUMN);

$stmt_task_tags = $pdo->prepare("SELECT tag_id FROM task_tags WHERE task_id = ?"); $stmt_task_tags->execute([$task_id]); $current_tags = $stmt_task_tags->fetchAll(PDO::FETCH_COLUMN);

$users = $pdo->query("SELECT id, full_name FROM users WHERE role != 'admin'")->fetchAll();
$all_tags = $pdo->query("SELECT * FROM tags")->fetchAll();

$existing_date = date('Y-m-d', strtotime($task['due_date']));
$existing_time = date('H:i', strtotime($task['due_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Task</h2>
            <a href="dashboard.php" class="text-gray-500 hover:text-blue-600"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>

        <form action="actions/edit_task_action.php" method="POST">
            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required class="w-full p-2 border rounded focus:border-blue-500 outline-none">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Assign To</label>
                <select name="assigned_to[]" multiple class="w-full p-2 border rounded bg-gray-50 h-24 text-sm focus:border-blue-500 outline-none">
                    <?php foreach($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo in_array($u['id'], $current_assignees) ? 'selected' : ''; ?>>
                            <?php echo $u['full_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4 bg-gray-50 p-3 rounded border">
                <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Tags</label>
                
                <div class="flex flex-wrap gap-2 mb-3">
                    <?php foreach($all_tags as $t): ?>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="tags[]" value="<?php echo $t['id']; ?>" class="sr-only peer" <?php echo in_array($t['id'], $current_tags) ? 'checked' : ''; ?>>
                            <span class="text-xs px-2 py-1 rounded border bg-white text-gray-600 peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 select-none transition">
                                <?php echo $t['name']; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="flex gap-2">
                    <input type="text" name="custom_tag_name" placeholder="Add new tag..." class="text-xs border p-2 rounded flex-1 focus:outline-none focus:border-blue-500">
                    <select name="custom_tag_color" class="text-xs border p-2 rounded focus:outline-none cursor-pointer">
                        <option value="gray">Gray</option>
                        <option value="red">Red</option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="yellow">Yellow</option>
                        <option value="purple">Purple</option>
                        <option value="pink">Pink</option>
                        <option value="indigo">Indigo</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Due Date</label>
                    <input type="date" name="due_date" value="<?php echo $existing_date; ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Due Time</label>
                    <input type="time" name="due_time" value="<?php echo $existing_time; ?>" required class="w-full p-2 border rounded">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Status</label>
                    <select name="status" class="w-full p-2 border rounded bg-white">
                        <option value="pending" <?php echo ($task['status']=='pending')?'selected':''; ?>>Pending</option>
                        <option value="in_progress" <?php echo ($task['status']=='in_progress')?'selected':''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($task['status']=='completed')?'selected':''; ?>>Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Priority</label>
                    <select name="priority" class="w-full p-2 border rounded bg-white">
                        <option value="low" <?php echo ($task['priority']=='low')?'selected':''; ?>>Low</option>
                        <option value="medium" <?php echo ($task['priority']=='medium')?'selected':''; ?>>Medium</option>
                        <option value="high" <?php echo ($task['priority']=='high')?'selected':''; ?>>High</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2 text-xs uppercase">Description</label>
                <textarea name="description" class="w-full p-2 border rounded h-32 focus:border-blue-500 outline-none"><?php echo htmlspecialchars($task['description']); ?></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700 shadow transition">Update Task</button>
        </form>
    </div>
</body>
</html>