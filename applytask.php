<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'worker') {
    echo "<script>alert('Login as worker to apply');window.location.href='login.php';</script>"; exit;
}
$task_id = intval($_GET['id'] ?? 0);
 
// check existing
$stmt = $mysqli->prepare("SELECT * FROM tasks WHERE id=? LIMIT 1");
$stmt->bind_param('i',$task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
if (!$task) { echo "<script>alert('Task not found');window.location.href='marketplace.php';</script>"; exit; }
 
$already = false;
$stmt2 = $mysqli->prepare("SELECT id,status FROM applications WHERE task_id=? AND worker_id=? LIMIT 1");
$stmt2->bind_param('ii',$task_id,$_SESSION['user_id']);
$stmt2->execute();
$r2 = $stmt2->get_result()->fetch_assoc();
if ($r2) $already = true;
 
if ($_SERVER['REQUEST_METHOD']==='POST') {
    // apply or submit result text
    if (!$already) {
        $ins = $mysqli->prepare("INSERT INTO applications (task_id,worker_id,status) VALUES (?,?, 'applied')");
        $ins->bind_param('ii',$task_id,$_SESSION['user_id']);
        if ($ins->execute()) {
            echo "<script>alert('Applied — wait for requester acceptance');window.location.href='worker_dashboard.php';</script>"; exit;
        } else { $err="DB error"; }
    } else {
        // submit work (mark submitted)
        $upd = $mysqli->prepare("UPDATE applications SET status='submitted', submitted_at=NOW() WHERE id=?");
        $upd->bind_param('i',$r2['id']);
        if ($upd->execute()) {
            echo "<script>alert('Work submitted — wait for review');window.location.href='worker_dashboard.php';</script>"; exit;
        } else { $err="DB error"; }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Apply</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:Inter;background:#071025;color:#e8fbff;padding:20px}
  .card{max-width:700px;margin:0 auto;background:rgba(255,255,255,0.02);padding:18px;border-radius:12px}
  .btn{padding:10px;border-radius:8px;border:none;cursor:pointer;background:#06b6d4;color:#021018}
</style>
</head>
<body>
  <div class="card">
    <h2><?=htmlspecialchars($task['title'])?></h2>
    <div class="muted">Payment: $<?=number_format($task['payment'],2)?></div>
    <?php if(!$already): ?>
      <p>Click apply to accept this task. After requester accepts, do the work and submit here.</p>
      <form method="post"><button class="btn" type="submit">Apply for task</button></form>
    <?php else: ?>
      <p>You already applied. You can submit your work now.</p>
      <form method="post">
        <label>Submission notes / link</label>
        <input name="notes" placeholder="Paste result link or notes (optional)">
        <div style="margin-top:10px"><button class="btn" type="submit">Submit work</button></div>
      </form>
    <?php endif; ?>
    <div style="margin-top:12px"><button onclick="window.location.href='marketplace.php'">Back to marketplace</button></div>
  </div>
</body>
</html>
