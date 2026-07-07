<?php
// actions/register_action.php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic Validation
    if (empty($name) || empty($email) || empty($password)) {
        die("Please fill all fields");
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);
        header("Location: ../index.php?success=registered");
        exit();
    } catch (PDOException $e) {
        // Handle Duplicate Email
        if ($e->getCode() == 23000) {
            echo "Email already exists. <a href='../register.php'>Try again</a>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>