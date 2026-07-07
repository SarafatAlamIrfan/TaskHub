<?php
session_start();
require '../includes/db.php';

// 1. Security Check: Only Admin can delete users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_to_delete = $_GET['id'];
    $current_user = $_SESSION['user_id'];

    // 2. Prevent Self-Deletion
    if ($user_to_delete == $current_user) {
        header("Location: ../dashboard.php?error=cannot_delete_self");
        exit();
    }

    try {
        // 3. Delete the User
        // Note: Because of ON DELETE CASCADE in database setup, 
        // this automatically removes their comments, files, and task assignments.
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_to_delete]);

        header("Location: ../dashboard.php?success=user_deleted");
    } catch (PDOException $e) {
        die("Error deleting user: " . $e->getMessage());
    }
} else {
    header("Location: ../dashboard.php");
}
?>