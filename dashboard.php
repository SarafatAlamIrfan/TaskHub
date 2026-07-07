<?php 
require 'includes/db.php'; 

// 1. Session Check
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];

// 2. Role Logic
$is_admin_lead = ($role == 'admin' || $role == 'lead');
$main_col_class = 'lg:col-span-3'; 

// 3. Helper Function: Time Ago
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);
    $string = array('y' => $diff->y, 'm' => $diff->m, 'w' => $weeks, 'd' => $days, 'h' => $diff->h, 'i' => $diff->i, 's' => $diff->s);
    $labels = array('y' => 'yr', 'm' => 'mon', 'w' => 'wk', 'd' => 'day', 'h' => 'hr', 'i' => 'min', 's' => 'sec');
    foreach ($string as $k => $v) {
        if ($v) $string[$k] = $v . ' ' . $labels[$k] . ($v > 1 ? 's' : ''); else unset($string[$k]);
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// 4. SORTING LOGIC
$sort_option = $_GET['sort'] ?? 'newest';
$order_sql = "t.created_at DESC"; // Default

switch ($sort_option) {
    case 'oldest': $order_sql = "t.created_at ASC"; break;
    case 'a-z': $order_sql = "t.title ASC"; break;
    case 'z-a': $order_sql = "t.title DESC"; break;
    case 'priority': $order_sql = "FIELD(t.priority, 'high', 'medium', 'low')"; break;
    case 'deadline': $order_sql = "t.due_date ASC"; break;
    default: $order_sql = "t.created_at DESC"; break;
}

// 5. Fetch Tasks
if ($is_admin_lead) {
    $sql = "SELECT t.*, GROUP_CONCAT(u.full_name SEPARATOR ', ') as assignee_names 
            FROM tasks t 
            LEFT JOIN task_assignments ta ON t.id = ta.task_id 
            LEFT JOIN users u ON ta.user_id = u.id 
            GROUP BY t.id 
            ORDER BY $order_sql";
    $stmt = $pdo->query($sql);
} else {
    $sql = "SELECT t.*, GROUP_CONCAT(u.full_name SEPARATOR ', ') as assignee_names 
            FROM tasks t 
            JOIN task_assignments ta_me ON t.id = ta_me.task_id 
            LEFT JOIN task_assignments ta_all ON t.id = ta_all.task_id 
            LEFT JOIN users u ON ta_all.user_id = u.id 
            WHERE ta_me.user_id = ? 
            GROUP BY t.id 
            ORDER BY $order_sql";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}
$tasks = $stmt->fetchAll();

// 6. Fetch Global Data
$all_users = $pdo->query("SELECT id, full_name, role FROM users")->fetchAll();
$all_tags = $pdo->query("SELECT * FROM tags")->fetchAll();

// 7. Stats
$my_stats = ['total' => 0, 'pending' => 0, 'completed' => 0];
if (!$is_admin_lead) {
    $my_stats['total'] = count($tasks);
    foreach($tasks as $t) {
        if($t['status'] == 'completed') $my_stats['completed']++;
        if($t['status'] == 'pending') $my_stats['pending']++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .p-high { border-left: 5px solid #ef4444; }
        .p-medium { border-left: 5px solid #eab308; }
        .p-low { border-left: 5px solid #22c55e; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans flex flex-col min-h-screen">

    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-layer-group text-blue-600 text-2xl"></i>
            <div>
                <h1 class="text-xl font-bold leading-none">TaskHub</h1>
                <span class="text-xs text-gray-400 font-bold uppercase"><?php echo $role; ?> Panel</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <?php if($role == 'admin'): ?>
                <a href="analytics.php" class="flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium transition">
                    <i class="fa-solid fa-chart-pie"></i> <span class="hidden md:inline">Analytics</span>
                </a>
            <?php endif; ?>
            <a href="profile.php" class="flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span class="hidden md:inline"><?php echo htmlspecialchars($name); ?></span>
            </a>
            <a href="actions/logout.php" class="text-red-500 hover:text-red-700 ml-2" title="Logout"><i class="fa-solid fa-sign-out-alt text-xl"></i></a>
        </div>
    </nav>

    <div class="container mx-auto p-6 grid grid-cols-1 lg:grid-cols-4 gap-6 flex-grow">
        
        <div class="<?php echo $main_col_class; ?>">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-bold text-gray-700">Task Board</h2>
                    
                    <form action="" method="GET">
                        <div class="relative">
                            <i class="fa-solid fa-arrow-down-short-wide absolute left-2 top-2 text-gray-400 text-xs"></i>
                            <select name="sort" onchange="this.form.submit()" class="pl-6 pr-2 py-1 border rounded bg-white text-xs text-gray-600 focus:outline-none focus:border-blue-500 shadow-sm cursor-pointer hover:bg-gray-50">
                                <option value="newest" <?php echo $sort_option == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort_option == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="deadline" <?php echo $sort_option == 'deadline' ? 'selected' : ''; ?>>Deadline (Soonest)</option>
                                <option value="priority" <?php echo $sort_option == 'priority' ? 'selected' : ''; ?>>Priority (High-Low)</option>
                                <option value="a-z" <?php echo $sort_option == 'a-z' ? 'selected' : ''; ?>>Title (A-Z)</option>
                                <option value="z-a" <?php echo $sort_option == 'z-a' ? 'selected' : ''; ?>>Title (Z-A)</option>
                            </select>
                        </div>
                    </form>
                </div>

                <?php if ($is_admin_lead): ?>
                <button onclick="document.getElementById('taskModal').classList.remove('hidden')" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> New Task
                </button>
                <?php endif; ?>
            </div>

            <?php if (count($tasks) == 0): ?>
                <div class="bg-white rounded-lg shadow p-10 text-center text-gray-400">
                    <i class="fa-regular fa-folder-open text-4xl mb-3"></i>
                    <p>No tasks found.</p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 <?php echo $is_admin_lead ? 'md:grid-cols-2' : 'md:grid-cols-2 lg:grid-cols-3'; ?> gap-6">
                
                <?php foreach ($tasks as $task): ?>
                <?php
                    // Data Fetching
                    $stmt_files = $pdo->prepare("SELECT * FROM task_files WHERE task_id = ?"); $stmt_files->execute([$task['id']]); $files = $stmt_files->fetchAll();
                    $stmt_comments = $pdo->prepare("SELECT c.*, u.full_name FROM task_comments c JOIN users u ON c.user_id = u.id WHERE c.task_id = ? ORDER BY c.created_at ASC"); $stmt_comments->execute([$task['id']]); $comments = $stmt_comments->fetchAll();
                    $stmt_sub = $pdo->prepare("SELECT * FROM subtasks WHERE task_id = ?"); $stmt_sub->execute([$task['id']]); $subtasks = $stmt_sub->fetchAll();
                    $stmt_logs = $pdo->prepare("SELECT l.*, u.full_name FROM task_logs l JOIN users u ON l.user_id = u.id WHERE task_id = ? ORDER BY created_at DESC LIMIT 5"); $stmt_logs->execute([$task['id']]); $logs = $stmt_logs->fetchAll();
                    $stmt_tags = $pdo->prepare("SELECT t.* FROM tags t JOIN task_tags tt ON t.id = tt.tag_id WHERE tt.task_id = ?"); $stmt_tags->execute([$task['id']]); $card_tags = $stmt_tags->fetchAll();
                    
                    // Calculations
                    $total_sub = count($subtasks);
                    $done_sub = count(array_filter($subtasks, fn($s) => $s['is_completed']));
                    $percent = $total_sub > 0 ? round(($done_sub / $total_sub) * 100) : 0;

                    $due_timestamp = strtotime($task['due_date']);
                    $now = time();
                    $diff_seconds = $due_timestamp - $now;
                    
                    $date_color = "text-gray-400"; 
                    $date_icon = "fa-clock";
                    
                    if ($task['status'] != 'completed') {
                        if ($diff_seconds < 0) {
                            $date_color = "text-red-600 font-bold animate-pulse"; 
                            $date_icon = "fa-circle-exclamation";
                        } elseif ($diff_seconds < 86400) {
                            $date_color = "text-orange-500 font-bold";
                            $date_icon = "fa-hourglass-half";
                        }
                    } else {
                        $date_color = "text-green-600 font-medium"; 
                        $date_icon = "fa-check-circle";
                    }
                ?>

                <div id="task-<?php echo $task['id']; ?>" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-5 border relative flex flex-col h-full p-<?php echo $task['priority']; ?>">
                    
                    <?php if ($is_admin_lead): ?>
                    <div class="absolute top-4 right-4 flex gap-2 bg-white bg-opacity-90 rounded pl-2 z-10">
                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="text-gray-400 hover:text-blue-600 transition"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a href="actions/delete_task.php?id=<?php echo $task['id']; ?>&current_sort=<?php echo $sort_option; ?>" onclick="return confirm('Delete task?')" class="text-gray-400 hover:text-red-600 transition"><i class="fa-solid fa-trash"></i></a>
                    </div>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-1 mb-3 pr-12">
                        <?php foreach($card_tags as $t): ?>
                            <span class="text-[10px] px-2 py-0.5 rounded border font-medium <?php echo $t['color_class']; ?>"><?php echo $t['name']; ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold px-2 py-1 rounded bg-gray-100 uppercase text-gray-600 tracking-wider"><?php echo $task['priority']; ?></span>
                        <span class="text-xs font-mono flex items-center gap-1 <?php echo $date_color; ?>">
                            <i class="fa-regular <?php echo $date_icon; ?>"></i> 
                            <?php echo date('d M, h:i A', $due_timestamp); ?>
                        </span>
                    </div>
                    
                    <h3 class="font-bold text-lg text-gray-800 mb-2 leading-snug"><?php echo htmlspecialchars($task['title']); ?></h3>
                    
                    <?php if($is_admin_lead && $total_sub > 0): ?>
                    <div class="mb-3">
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <p class="text-gray-500 text-xs mb-4 h-10 overflow-hidden leading-relaxed"><?php echo htmlspecialchars($task['description']); ?></p>
                    
                    <div class="pt-3 border-t border-gray-100 mt-auto">
                        
                        <div class="text-xs text-gray-500 mb-2 truncate">
                            To: <span class="font-semibold text-gray-800"><?php echo $task['assignee_names'] ?: 'Unassigned'; ?></span>
                        </div>

                        <form action="actions/update_task.php" method="POST" class="w-full mb-3">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="current_sort" value="<?php echo htmlspecialchars($sort_option); ?>">
                            <select name="status" onchange="this.form.submit()" 
                                    class="text-xs border rounded px-2 py-1 w-full bg-white cursor-pointer focus:outline-none shadow-sm font-medium">
                                <option value="pending" <?php echo $task['status']=='pending'?'selected':''; ?>>🔴 Pending</option>
                                <option value="in_progress" <?php echo $task['status']=='in_progress'?'selected':''; ?>>🟡 In Progress</option>
                                <option value="completed" <?php echo $task['status']=='completed'?'selected':''; ?>>🟢 Completed</option>
                            </select>
                        </form>

                        <div class="bg-blue-50 p-2 rounded mb-3 border border-blue-100">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-[10px] font-bold text-blue-600 uppercase"><i class="fa-solid fa-list-check"></i> Subtasks</p>
                            </div>
                            <ul class="space-y-1 mb-2">
                                <?php foreach($subtasks as $s): ?>
                                <li class="flex items-center gap-2 group">
                                    <form action="actions/manage_subtask.php" method="POST" class="flex items-center gap-2 flex-1">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="subtask_id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <input type="hidden" name="current_sort" value="<?php echo htmlspecialchars($sort_option); ?>">
                                        
                                        <input type="checkbox" onchange="this.form.submit()" class="cursor-pointer accent-blue-600" <?php echo $s['is_completed'] ? 'checked' : ''; ?>>
                                        <span class="text-xs text-gray-700 break-all <?php echo $s['is_completed'] ? 'line-through text-gray-400' : ''; ?>">
                                            <?php echo htmlspecialchars($s['title']); ?>
                                        </span>
                                    </form>
                                    <?php if($is_admin_lead): ?>
                                        <a href="actions/manage_subtask.php?action=delete&id=<?php echo $s['id']; ?>&current_sort=<?php echo $sort_option; ?>" class="text-gray-400 hover:text-red-500 transition px-1"><i class="fa-solid fa-xmark"></i></a>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if($is_admin_lead): ?>
                            <form action="actions/manage_subtask.php" method="POST" class="flex gap-1">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="current_sort" value="<?php echo htmlspecialchars($sort_option); ?>">
                                <input type="text" name="title" placeholder="Add subtask..." class="w-full text-[10px] border p-1 rounded focus:outline-none focus:border-blue-400">
                                <button class="text-blue-600 hover:text-blue-800 text-xs px-1"><i class="fa-solid fa-plus"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <div class="bg-gray-50 p-2 rounded mb-3 border border-gray-200">
                            <p class="text-[10px] font-bold text-gray-500 uppercase mb-1"><i class="fa-solid fa-paperclip"></i> Files</p>
                            <ul class="mb-2 space-y-1">
                                <?php foreach($files as $f): ?>
                                    <li class="flex items-center gap-2">
                                        <a href="uploads/<?php echo $f['file_path']; ?>" target="_blank" class="text-xs text-blue-600 hover:underline truncate w-full block">
                                            <?php echo htmlspecialchars($f['filename']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <form action="actions/upload_file.php" method="POST" enctype="multipart/form-data" class="flex gap-1">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="current_sort" value="<?php echo htmlspecialchars($sort_option); ?>">
                                <input type="file" name="file" required class="w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 transition">
                                <button class="text-blue-600 hover:text-blue-800"><i class="fa-solid fa-upload"></i></button>
                            </form>
                        </div>
                        
                        <?php if($is_admin_lead): ?>
                        <details class="group bg-white border border-gray-200 p-2 rounded mb-3">
                            <summary class="list-none cursor-pointer text-[10px] font-bold text-gray-500 uppercase flex items-center gap-1 select-none hover:text-blue-600 transition">
                                <i class="fa-solid fa-clock-rotate-left"></i> History <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform ml-auto text-gray-400"></i>
                            </summary>
                            <div class="mt-2 border-t pt-2 space-y-2">
                                <?php if (count($logs) > 0): foreach($logs as $log): ?>
                                    <div class="text-[10px]">
                                        <p class="text-gray-800 leading-tight"><span class="font-bold"><?php echo htmlspecialchars($log['full_name']); ?></span> <?php echo htmlspecialchars($log['action_text']); ?></p>
                                        <p class="text-gray-400"><?php echo time_elapsed_string($log['created_at']); ?></p>
                                    </div>
                                <?php endforeach; else: ?>
                                    <p class="text-[10px] text-gray-400 italic text-center">No history yet.</p>
                                <?php endif; ?>
                            </div>
                        </details>
                        <?php endif; ?>

                        <details class="group bg-white border border-gray-200 p-2 rounded open">
                            <summary class="list-none cursor-pointer text-[10px] font-bold text-gray-500 uppercase flex items-center gap-1 select-none hover:text-blue-600 transition">
                                <i class="fa-regular fa-comments"></i> Comments <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform ml-auto text-gray-400"></i>
                            </summary>
                            <div class="mt-3 border-t pt-2">
                                <div class="max-h-24 overflow-y-auto mb-2 space-y-2 pr-1 custom-scrollbar">
                                    <?php foreach($comments as $c): ?>
                                        <div class="bg-gray-50 p-2 rounded text-xs">
                                            <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                                                <span class="font-bold text-gray-600"><?php echo htmlspecialchars($c['full_name']); ?></span>
                                                <span><?php echo time_elapsed_string($c['created_at']); ?></span>
                                            </div>
                                            <p class="text-gray-700 break-words"><?php echo htmlspecialchars($c['comment']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if(count($comments)==0): ?><p class="text-[10px] text-gray-400 italic text-center py-2">No comments.</p><?php endif; ?>
                                </div>
                                <form action="actions/add_comment.php" method="POST" class="flex gap-1">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="hidden" name="current_sort" value="<?php echo htmlspecialchars($sort_option); ?>">
                                    <input type="text" name="comment" placeholder="Write a comment..." required class="w-full border rounded px-2 py-1 text-xs focus:outline-none focus:border-blue-500">
                                    <button class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs hover:bg-gray-300 transition"><i class="fa-solid fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </details>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="lg:col-span-1">
            <?php if ($is_admin_lead): ?>
            <div class="bg-white rounded-xl shadow p-6 sticky top-24">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Team Members</h3>
                <ul class="space-y-4">
                    <?php foreach($all_users as $u): ?>
                    <li class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-sm <?php echo ($u['role']=='admin') ? 'bg-purple-500' : 'bg-blue-500'; ?>">
                                <?php echo strtoupper(substr($u['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 leading-none"><?php echo htmlspecialchars($u['full_name']); ?></p>
                                <span class="text-[10px] text-gray-500 capitalize bg-gray-100 px-2 py-0.5 rounded-full mt-1 inline-block"><?php echo $u['role']; ?></span>
                            </div>
                        </div>
                        <?php if($role == 'admin' && $u['id'] != $user_id): ?>
                            <a href="actions/delete_user.php?id=<?php echo $u['id']; ?>" onclick="return confirm('Remove user?')" class="text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100" title="Remove User"><i class="fa-solid fa-user-minus"></i></a>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-xl shadow p-6 sticky top-24">
                <div class="flex items-center gap-3 mb-4 border-b pb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg"><?php echo strtoupper(substr($name, 0, 1)); ?></div>
                    <div>
                        <h3 class="font-bold text-gray-800 leading-tight">My Performance</h3>
                        <p class="text-xs text-gray-500">Task Overview</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="bg-blue-50 p-3 rounded-lg flex justify-between items-center"><span class="text-sm text-blue-700 font-medium">Total Tasks</span><span class="text-lg font-bold text-blue-800"><?php echo $my_stats['total']; ?></span></div>
                    <div class="bg-green-50 p-3 rounded-lg flex justify-between items-center"><span class="text-sm text-green-700 font-medium">Completed</span><span class="text-lg font-bold text-green-800"><?php echo $my_stats['completed']; ?></span></div>
                    <div class="bg-yellow-50 p-3 rounded-lg flex justify-between items-center"><span class="text-sm text-yellow-700 font-medium">Pending</span><span class="text-lg font-bold text-yellow-800"><?php echo $my_stats['pending']; ?></span></div>
                </div>
                <div class="mt-6 pt-4 border-t text-center"><p class="text-xs text-gray-400 italic">Keep up the good work!</p></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-white border-t mt-auto">
        <div class="container mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
            <div class="mb-2 md:mb-0">&copy; <?php echo date('Y'); ?> <strong>TaskHub</strong>. All rights reserved.</div>
            <div class="flex items-center gap-4"><a href="#" class="hover:text-blue-600">Privacy</a><span>|</span><a href="#" class="hover:text-blue-600">Terms</a><span>|</span><span>CSE Project</span></div>
        </div>
    </footer>

    <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white p-6 rounded-xl w-11/12 md:w-1/2 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Create New Task</h2>
                <button onclick="document.getElementById('taskModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="actions/create_task.php" method="POST">
                <div class="space-y-4">
                    <input type="text" name="title" placeholder="Task Title" required class="border p-2 rounded w-full focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none">
                    
                    <div class="bg-gray-50 p-3 rounded border mb-4">
                        <label class="text-xs font-bold text-gray-500 uppercase block mb-2">Manage Tags</label>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <?php foreach($all_tags as $t): ?>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="tags[]" value="<?php echo $t['id']; ?>" class="sr-only peer">
                                    <span class="text-xs px-2 py-1 rounded border bg-white text-gray-600 peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 select-none transition"><?php echo $t['name']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" name="custom_tag_name" placeholder="Type new tag name..." class="text-xs border p-2 rounded flex-1 focus:outline-none focus:border-blue-500">
                            <select name="custom_tag_color" class="text-xs border p-2 rounded focus:outline-none cursor-pointer bg-white">
                                <option value="gray">Gray</option><option value="red">Red</option><option value="blue">Blue</option><option value="green">Green</option><option value="yellow">Yellow</option><option value="purple">Purple</option><option value="pink">Pink</option><option value="indigo">Indigo</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase block mb-1">Assign To (Hold Ctrl/Cmd)</label>
                        <select name="assigned_to[]" multiple required class="w-full border p-2 rounded h-24 bg-gray-50 focus:border-blue-500 outline-none text-sm">
                            <?php foreach($all_users as $u): if($u['role'] != 'admin'): ?><option value="<?php echo $u['id']; ?>"><?php echo $u['full_name']; ?></option><?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <select name="status" class="w-full border p-2 rounded mt-1 bg-white"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select>
                        <select name="priority" class="w-full border p-2 rounded mt-1 bg-white"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" name="due_date" required class="w-full border p-2 rounded mt-1">
                        <input type="time" name="due_time" required class="w-full border p-2 rounded mt-1">
                    </div>
                    <textarea name="description" placeholder="Description..." class="border p-2 rounded h-24 w-full mt-1 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="document.getElementById('taskModal').classList.add('hidden')" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 shadow font-semibold transition">Create Task</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Save scroll position before reload
        window.onbeforeunload = function() {
            sessionStorage.setItem('scrollpos', window.scrollY);
        };

        // Restore scroll position on load
        document.addEventListener("DOMContentLoaded", function() { 
            var scrollpos = sessionStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
        });
    </script>

</body>
</html>