<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];
    
    // Capture Sort Order
    $current_sort = $_POST['current_sort'] ?? 'newest';

    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->execute([$status, $task_id]);

        $status_text = strtoupper(str_replace('_', ' ', $status));
        logActivity($pdo, $task_id, $user_id, "Changed status to $status_text");

        // Redirect with Sort Parameter
        header("Location: ../dashboard.php?sort=" . urlencode($current_sort));
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>