<?php
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

$action = $_POST['action'] ?? $_GET['action'];
$user_role = $_SESSION['role'];

// 1. Capture the current sort order (from POST or GET)
$current_sort = $_REQUEST['current_sort'] ?? 'newest';

if ($action == 'add' && ($user_role == 'admin' || $user_role == 'lead')) {
    $task_id = $_POST['task_id'];
    $title = trim($_POST['title']);
    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, title) VALUES (?, ?)");
        $stmt->execute([$task_id, $title]);
    }
} 
elseif ($action == 'toggle') {
    $subtask_id = $_POST['subtask_id'];
    
    // Get current status
    $stmt = $pdo->prepare("SELECT is_completed FROM subtasks WHERE id = ?");
    $stmt->execute([$subtask_id]);
    $current = $stmt->fetchColumn();
    
    // Toggle
    $new_status = $current ? 0 : 1;
    $pdo->prepare("UPDATE subtasks SET is_completed = ? WHERE id = ?")->execute([$new_status, $subtask_id]);
} 
elseif ($action == 'delete' && ($user_role == 'admin' || $user_role == 'lead')) {
    $id = $_GET['id'];
    $pdo->prepare("DELETE FROM subtasks WHERE id = ?")->execute([$id]);
}

// 2. Redirect WITH the sort parameter
header("Location: ../dashboard.php?sort=" . urlencode($current_sort));
?>