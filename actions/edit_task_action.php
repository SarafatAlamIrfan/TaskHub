<?php
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'lead')) {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['task_id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $assigned_users = $_POST['assigned_to'] ?? [];
    $selected_tags = $_POST['tags'] ?? [];
    
    // Custom Tag Logic
    $custom_tag_name = trim($_POST['custom_tag_name']);
    $custom_tag_color = $_POST['custom_tag_color'];

    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $date_input = $_POST['due_date'];
    $time_input = $_POST['due_time'];
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

        // 1. Update Task
        $sql = "UPDATE tasks SET title=?, description=?, priority=?, status=?, due_date=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $desc, $priority, $status, $final_due_date, $id]);

        // 2. Update Assignments
        $pdo->prepare("DELETE FROM task_assignments WHERE task_id = ?")->execute([$id]);
        $stmt_ins = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)");
        foreach ($assigned_users as $uid) $stmt_ins->execute([$id, $uid]);

        // 3. Handle Custom Tag
        if (!empty($custom_tag_name)) {
            $stmt_check = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt_check->execute([$custom_tag_name]);
            $existing_tag = $stmt_check->fetch();

            if ($existing_tag) {
                $new_tag_id = $existing_tag['id'];
            } else {
                $color_class = $colors[$custom_tag_color] ?? $colors['gray'];
                $stmt_new_tag = $pdo->prepare("INSERT INTO tags (name, color_class) VALUES (?, ?)");
                $stmt_new_tag->execute([$custom_tag_name, $color_class]);
                $new_tag_id = $pdo->lastInsertId();
            }
            $selected_tags[] = $new_tag_id;
        }

        // 4. Update Tags
        $pdo->prepare("DELETE FROM task_tags WHERE task_id = ?")->execute([$id]);
        $stmt_ins_tags = $pdo->prepare("INSERT INTO task_tags (task_id, tag_id) VALUES (?, ?)");
        foreach (array_unique($selected_tags) as $tag_id) $stmt_ins_tags->execute([$id, $tag_id]);

        $pdo->commit();
        header("Location: ../dashboard.php?success=edited");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>