<?php
// includes/db.php

$host = 'localhost';
$dbname = 'taskhub_db';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    date_default_timezone_set('Asia/Dhaka');
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// HELPER FUNCTION: Log Activity
function logActivity($pdo, $task_id, $user_id, $text) {
    try {
        $stmt = $pdo->prepare("INSERT INTO task_logs (task_id, user_id, action_text) VALUES (?, ?, ?)");
        $stmt->execute([$task_id, $user_id, $text]);
    } catch (Exception $e) {
        // Silently fail logging to not break the app
    }
}
?>