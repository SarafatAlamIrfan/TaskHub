<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    
    // Capture Sort Order
    $current_sort = $_POST['current_sort'] ?? 'newest';

    $upload_dir = '../uploads/';
    $filename = basename($_FILES['file']['name']);
    $target_file = $upload_dir . time() . "_" . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $clean_filename = time() . "_" . $filename;
        $stmt = $pdo->prepare("INSERT INTO task_files (task_id, user_id, filename, file_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$task_id, $user_id, $filename, $clean_filename]);

        logActivity($pdo, $task_id, $user_id, "uploaded a file: $filename");
    }

    // Redirect with Sort Parameter
    header("Location: ../dashboard.php?sort=" . urlencode($current_sort));
}
?>