<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'lead')) {
    
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $assigned_users = $_POST['assigned_to'] ?? [];
    $selected_tags = $_POST['tags'] ?? []; // Existing checkboxes
    
    // Custom Tag Logic
    $custom_tag_name = trim($_POST['custom_tag_name']);
    $custom_tag_color = $_POST['custom_tag_color'];

    $date_input = $_POST['due_date'];
    $time_input = $_POST['due_time'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $assigned_by = $_SESSION['user_id'];
    $final_due_date = $date_input . ' ' . $time_input;

    // Color Mapping
    $colors = [
        'red' => 'bg-red-100 text-red-800 border-red-200',
        'blue' => 'bg-blue-100 text-blue-800 border-blue-200',
        'green' => 'bg-green-100 text-green-800 border-green-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
        'pink' => 'bg-pink-100 text-pink-800 border-pink-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
    ];

    try {
        $pdo->beginTransaction();

        // 1. Create Task
        $sql = "INSERT INTO tasks (title, description, assigned_by, due_date, priority, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $desc, $assigned_by, $final_due_date, $priority, $status]);
        $task_id = $pdo->lastInsertId();

        // 2. Handle Custom Tag Creation
        if (!empty($custom_tag_name)) {
            // Check if tag exists to avoid duplicates
            $stmt_check = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt_check->execute([$custom_tag_name]);
            $existing_tag = $stmt_check->fetch();

            if ($existing_tag) {
                $new_tag_id = $existing_tag['id'];
            } else {
                // Create new tag
                $color_class = $colors[$custom_tag_color] ?? $colors['gray'];
                $stmt_new_tag = $pdo->prepare("INSERT INTO tags (name, color_class) VALUES (?, ?)");
                $stmt_new_tag->execute([$custom_tag_name, $color_class]);
                $new_tag_id = $pdo->lastInsertId();
            }
            // Add new tag to selection list
            $selected_tags[] = $new_tag_id;
        }

        // 3. Assign Users
        $stmt_assign = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)");
        foreach ($assigned_users as $uid) {
            $stmt_assign->execute([$task_id, $uid]);
        }

        // 4. Assign Tags (Both selected and custom)
        $stmt_tag = $pdo->prepare("INSERT INTO task_tags (task_id, tag_id) VALUES (?, ?)");
        foreach (array_unique($selected_tags) as $tag_id) {
            $stmt_tag->execute([$task_id, $tag_id]);
        }

        $pdo->commit();
        header("Location: ../dashboard.php?success=created");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>