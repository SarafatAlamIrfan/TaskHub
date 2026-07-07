<?php
require 'includes/db.php';

// 1. Security: Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// --- DATA FETCHING ---

// A. Task Status Distribution (Pie Chart)
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
$status_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['pending' => 5, 'completed' => 3]

// Ensure all keys exist for the chart array
$statuses = ['pending', 'in_progress', 'completed'];
$status_counts = [];
foreach ($statuses as $s) {
    $status_counts[] = $status_data[$s] ?? 0;
}

// B. Priority Distribution (Doughnut Chart)
$stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority");
$priority_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$priorities = ['low', 'medium', 'high'];
$priority_counts = [];
foreach ($priorities as $p) {
    $priority_counts[] = $priority_data[$p] ?? 0;
}

// C. Member Performance (Bar Chart & Table)
// Complex Query: Get User Name, Total Tasks, and Completed Tasks
$sql_performance = "SELECT 
                        u.full_name,
                        COUNT(ta.task_id) as total_tasks,
                        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
                    FROM users u
                    LEFT JOIN task_assignments ta ON u.id = ta.user_id
                    LEFT JOIN tasks t ON ta.task_id = t.id
                    WHERE u.role != 'admin'
                    GROUP BY u.id";
$stmt = $pdo->query($sql_performance);
$members = $stmt->fetchAll();

// Prepare arrays for Chart.js
$member_names = [];
$member_completed = [];
$member_total = [];

foreach ($members as $m) {
    $member_names[] = $m['full_name'];
    $member_completed[] = $m['completed_tasks'];
    $member_total[] = $m['total_tasks'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TaskHub Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-chart-pie text-purple-600 text-2xl"></i>
            <h1 class="text-xl font-bold">Analytics Dashboard</h1>
        </div>
        <a href="dashboard.php" class="text-gray-500 hover:text-blue-600 font-medium">
            <i class="fa-solid fa-arrow-left"></i> Back to Board
        </a>
    </nav>

    <div class="container mx-auto p-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Task Status Overview</h3>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Task Priority Distribution</h3>
                <div class="h-64">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Member Performance (Tasks Completed vs Total)</h3>
            <div class="h-80">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Detailed Member Statistics</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase">
                            <th class="p-3 border-b">Member Name</th>
                            <th class="p-3 border-b text-center">Total Assigned</th>
                            <th class="p-3 border-b text-center">Completed</th>
                            <th class="p-3 border-b text-center">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($members as $m): 
                            $rate = $m['total_tasks'] > 0 ? round(($m['completed_tasks'] / $m['total_tasks']) * 100) : 0;
                        ?>
                        <tr class="hover:bg-gray-50 border-b last:border-0 text-sm">
                            <td class="p-3 font-medium"><?php echo htmlspecialchars($m['full_name']); ?></td>
                            <td class="p-3 text-center"><?php echo $m['total_tasks']; ?></td>
                            <td class="p-3 text-center text-green-600 font-bold"><?php echo $m['completed_tasks']; ?></td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $rate; ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500"><?php echo $rate; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // 1. Status Chart (Pie)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: ['Pending', 'In Progress', 'Completed'],
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: ['#ef4444', '#eab308', '#22c55e'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 2. Priority Chart (Doughnut)
        const ctxPriority = document.getElementById('priorityChart').getContext('2d');
        new Chart(ctxPriority, {
            type: 'doughnut',
            data: {
                labels: ['Low', 'Medium', 'High'],
                datasets: [{
                    data: <?php echo json_encode($priority_counts); ?>,
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 3. Performance Chart (Bar)
        const ctxPerf = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctxPerf, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($member_names); ?>,
                datasets: [
                    {
                        label: 'Completed Tasks',
                        data: <?php echo json_encode($member_completed); ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    },
                    {
                        label: 'Total Assigned',
                        data: <?php echo json_encode($member_total); ?>,
                        backgroundColor: '#e5e7eb',
                        borderRadius: 4,
                        hidden: true // Hidden by default to keep it clean
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    </script>

</body>
</html>