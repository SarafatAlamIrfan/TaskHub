<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    // Capture Sort Order
    $current_sort = $_POST['current_sort'] ?? 'newest';

    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$task_id, $user_id, $comment]);
        
        logActivity($pdo, $task_id, $user_id, "commented on this task");
    }

    // Redirect with Sort Parameter
    header("Location: ../dashboard.php?sort=" . urlencode($current_sort));
}
?>