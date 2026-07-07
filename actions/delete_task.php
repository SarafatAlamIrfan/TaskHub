<?php
require '../includes/db.php';

// 1. Security Check: Only Admin or Lead can delete
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'lead')) {
    header("Location: ../dashboard.php");
    exit();
}

// 2. Check if ID is provided
if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    
    // Capture the current sort order so the page doesn't reset to 'Newest'
    $current_sort = $_GET['current_sort'] ?? 'newest';

    try {
        // 3. Delete the Task
        // (If your database is set up with ON DELETE CASCADE, this will also delete related subtasks/comments)
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);

        // 4. Redirect back with the sort order preserved
        header("Location: ../dashboard.php?sort=" . urlencode($current_sort) . "&msg=deleted");
        exit();

    } catch (PDOException $e) {
        die("Error deleting task: " . $e->getMessage());
    }
} else {
    // If no ID provided, just go back
    header("Location: ../dashboard.php");
    exit();
}
?>