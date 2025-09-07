<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'requester') {
    echo "<script>alert('Login as requester');window.location.href='login.php';</script>"; exit;
}
$rid = $_SESSION['user_id'];
// tasks
$stmt = $mysqli->prepare("SELECT * FROM tasks WHERE requester_id=? ORDER BY created_at DESC");
$stmt->bind_param('i',$rid); $stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Requester Dashboard</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:Inter;background:#071428;color:#e8fbff;padding:18px}
  .wrap{max-width:1000px;margin:0 auto}
  .card{background:rgba(255,255,255,0.02);padding:14px;border-radius:10px;margin-bottom:12px}
  .btn{padding:8px;border-radius:8px;border:none;cursor:pointer;background:#06b6d4;color:#021018}
  table{width:100%;border-collapse:collapse}
  td,th{padding:10px;border-bottom:1px solid rgba(255,255,255,0.02)}
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h3>Your tasks</h3>
      <table>
        <thead><tr><th>Title</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($tasks as $t): ?>
          <tr>
            <td><?=htmlspecialchars($t['title'])?></td>
            <td>$<?=number_format($t['payment'],2)?></td>
            <td><?=htmlspecialchars($t['status'])?></td>
            <td>
              <button onclick="window.location.href='manage_task.php?id=<?= $t['id'] ?>'">Manage</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div>
      <button onclick="window.location.href='post_task.php'">Post new task</button>
      <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </div>
</body>
</html>
 
