<?php
session_start();
require '../includes/db.php';

// Only Admin can manage tags
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$action = $_POST['action'] ?? $_GET['action'];

if ($action == 'add') {
    $name = trim($_POST['tag_name']);
    $color_key = $_POST['tag_color'];

    // Map selection to Tailwind Classes
    $colors = [
        'red'    => 'bg-red-100 text-red-800 border-red-200',
        'blue'   => 'bg-blue-100 text-blue-800 border-blue-200',
        'green'  => 'bg-green-100 text-green-800 border-green-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
        'pink'   => 'bg-pink-100 text-pink-800 border-pink-200',
        'gray'   => 'bg-gray-100 text-gray-800 border-gray-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
    ];

    $color_class = $colors[$color_key] ?? $colors['gray'];

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO tags (name, color_class) VALUES (?, ?)");
        $stmt->execute([$name, $color_class]);
    }
    header("Location: ../manage_tags.php?success=added");
} 

elseif ($action == 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../manage_tags.php?success=deleted");
}
?>