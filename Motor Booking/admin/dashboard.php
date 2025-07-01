<?php 
session_start();
include __DIR__ . '/../components/connect.php';

if (!isset($_SESSION['admin_id']) && !isset($_COOKIE['admin_id'])){
   header('location:login.php');
   exit;
}

$admin_id = $_SESSION['admin_id'] ?? $_COOKIE['admin_id'] ?? '';

function getCount($table, $where = '', $params = []) {
   global $conn;
   $allowedTables = ['bookings', 'users', 'messages', 'admins'];
   if (!in_array($table, $allowedTables)) return 0;

   $sql = "SELECT COUNT(*) FROM `$table` $where";
   $stmt = $conn->prepare($sql);
   $stmt->execute($params);
   return $stmt->fetchColumn();
}

function getServiceCounts($conn) {
   $sql = "SELECT service, COUNT(*) as count FROM bookings GROUP BY service";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserStatusCounts($conn) {
   $sql = "SELECT verified, COUNT(*) as count FROM users GROUP BY verified";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
   return [
      'pending'  => $results[1] ?? 0,
      'verified' => $results[2] ?? 0,
      'rejected' => $results[3] ?? 0, 
   ];
}


function getDailyStats($conn) {
   $sql = "SELECT check_in, complete, COUNT(*) as count FROM bookings WHERE verified = 1 GROUP BY check_in, complete ORDER BY check_in ASC";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $formatted = [];
   foreach ($data as $row) {
      $date = $row['check_in'];
      $status = $row['complete'] == 1 ? 'complete' : ($row['complete'] == 2 ? 'cancelled' : 'other');
      if (!isset($formatted[$date])) $formatted[$date] = ['complete' => 0, 'cancelled' => 0];
      if ($status === 'complete') $formatted[$date]['complete'] += $row['count'];
      if ($status === 'cancelled') $formatted[$date]['cancelled'] += $row['count'];
   }
   return $formatted;
}

$pendingBookings = getCount('bookings', 'WHERE verified = 0');
$totalCompleted = getCount('bookings', 'WHERE complete = 1 AND verified = 1');
$totalBookings = getCount('bookings', 'WHERE verified = 1');
$totalCancelled = getCount('bookings', 'WHERE complete = 2 AND verified = 1');
$serviceStats = getServiceCounts($conn);
$dailyStats = getDailyStats($conn);


$completionRate = $totalBookings > 0 ? round(($totalCompleted / $totalBookings) * 100, 1) : 0;
$cancellationRate = $totalBookings > 0 ? round(($totalCancelled / $totalBookings) * 100, 1) : 0;
$userStatusCounts = getUserStatusCounts($conn);
$verifiedUsers = $userStatusCounts['verified'];
$rejectedUsers = $userStatusCounts['rejected'];
$pendingUsers = $userStatusCounts['pending'];


$serviceNames = [
   1 => 'Change Oil',
   2 => 'CVT Cleaning',
   3 => 'Front Shock Repack',
   4 => 'Brake Cleaning',
   5 => 'Parts and Accessories',
   6 => 'Vulcanizing',
   7 => 'Magneto Repaint',
   8 => 'FI Cleaning',
   9 => 'Sensor Diagnotics',
   10 => 'PMS (Preventive Maintenace Service)',
];

$serviceLabels = [];
$serviceCounts = [];

foreach ($serviceStats as $s) {
   $label = $serviceNames[$s['service']] ?? 'Unknown';
   $serviceLabels[] = $label;
   $serviceCounts[] = $s['count'];
}


$statusLabels = array_keys($dailyStats);
$statusComplete = array_column($dailyStats, 'complete');
$statusCancelled = array_column($dailyStats, 'cancelled');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <link rel="stylesheet" href="../css/admin_style.css">
  <style>
    body { background: #f9fafb; font-family: sans-serif; margin: 0; padding: 0; }

    .dashboard { 
      max-width: 1200px; 
      margin: auto; 
      padding: 2rem; }
    .heading { 
      font-size: 2rem; 
      font-weight: 600; 
      margin-bottom: 1.5rem; }
    .box-container, .metrics-row {
       display: flex; 
       flex-wrap: wrap; 
       gap: 1rem; 
       margin-bottom: 2rem; }
    .box, .metric {
       background: #fff;
       padding: 1rem;
        border-radius: 10px; 
       flex: 1 1 250px; 
       text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .chart-row {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        justify-content: space-between;
        margin-bottom: 2rem;
      }
      .chart-box {
        flex: 1 1 45%;
        background: #fff;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        max-width: 550px;
      }

      canvas {
        width: 100% !important;
        height: auto !important;
      }

  </style>
</head>
<body>
  <?php include '../components/admin_header.php'; ?>
<div class="dashboard">
  <div class="box-container">
    <div class="box"><h3><?= $totalCompleted ?></h3><p>Completed Bookings</p></div>
    <div class="box"><h3><?= $totalCancelled ?></h3><p>Cancelled Bookings</p></div>
    <div class="box"><h3><?= $pendingBookings ?></h3><p>Pending Bookings</p></div>
  </div>
  <div class="chart-row">
  <div class="chart-box">
    <canvas id="serviceChart"></canvas>
  </div>
  <div class="chart-box">
    <canvas id="statusLineChart"></canvas>
    <div class="box-container">
  <div class="box">
    <h3><?= $completionRate ?>%</h3>
    <p>Completion Rate</p>
  </div>
  <div class="box">
    <h3><?= $cancellationRate ?>%</h3>
    <p>Cancellation Rate</p>
  </div>
</div>


  </div>
</div>

<div class="box-container">
  <div class="box"><h3><?= $verifiedUsers ?></h3><p>Verified Users</p></div>
  <div class="box"><h3><?= $rejectedUsers ?></h3><p>Rejected Users</p></div>
  <div class="box"><h3><?= $pendingUsers ?></h3><p>Pending Users</p></div>
</div>



  </div>
</div>
<script>
window.onload = function() {
  const serviceCtx = document.getElementById('serviceChart');
  if (serviceCtx) {
    new Chart(serviceCtx, {
      type: 'doughnut',
      data: { labels: <?= json_encode($serviceLabels) ?>, datasets: [{ data: <?= json_encode($serviceCounts) ?>, backgroundColor: ['#0ea5e9','#facc15','#10b981','#dc2626'] }] },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
  }

  const statusCtx = document.getElementById('statusLineChart');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [
          { label: 'Completed', data: <?= json_encode($statusComplete) ?>, borderColor: 'green', backgroundColor: 'rgba(25, 144, 14, 0.58)', tension: 0.4, fill: true },
          { label: 'Cancelled', data: <?= json_encode($statusCancelled) ?>, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.1)', tension: 0.4, fill: true }
        ]
      },
      options: { plugins: { legend: { position: 'top' } } }
    });
  }
}

</script>
</body>
</html>
