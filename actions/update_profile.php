<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['full_name'];
    $password = $_POST['password'];

    try {
        if (!empty($password)) {
            // Update Name AND Password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $hashed, $user_id]);
        } else {
            // Update Name ONLY
            $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            $stmt->execute([$name, $user_id]);
        }

        // Update Session Name
        $_SESSION['name'] = $name;
        
        header("Location: ../profile.php?success=1");
    } catch (PDOException $e) {
        die("Error updating profile: " . $e->getMessage());
    }
}
?>