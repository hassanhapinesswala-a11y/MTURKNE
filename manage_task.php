<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'requester') {
    echo "<script>alert('Login as requester');window.location.href='login.php';</script>"; exit;
}
$rid = $_SESSION['user_id'];
$task_id = intval($_GET['id'] ?? 0);
 
// verify ownership
$stmt = $mysqli->prepare("SELECT * FROM tasks WHERE id=? AND requester_id=? LIMIT 1");
$stmt->bind_param('ii',$task_id,$rid);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
if (!$task) { echo "<script>alert('Task not found or unauthorized');window.location.href='requester_dashboard.php';</script>"; exit; }
 
// handle accept/reject/pay actions via POST
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';
    $app_id = intval($_POST['app_id'] ?? 0);
    if ($action==='accept') {
        $upd = $mysqli->prepare("UPDATE applications SET status='accepted' WHERE id=?");
        $upd->bind_param('i',$app_id); $upd->execute();
        echo "<script>alert('Worker accepted');window.location.href='manage_task.php?id={$task_id}';</script>"; exit;
    } elseif ($action==='reject') {
        $upd = $mysqli->prepare("UPDATE applications SET status='rejected' WHERE id=?");
        $upd->bind_param('i',$app_id); $upd->execute();
        echo "<script>alert('Worker rejected');window.location.href='manage_task.php?id={$task_id}';</script>"; exit;
    } elseif ($action==='approve_payment') {
        // transfer payment to worker: mark task completed, set application status to submitted->paid (here accepted->submitted), add transaction and update balances
        $app = $mysqli->prepare("SELECT worker_id FROM applications WHERE id=? LIMIT 1");
        $app->bind_param('i',$app_id); $app->execute(); $aw = $app->get_result()->fetch_assoc();
        if ($aw) {
            $worker_id = $aw['worker_id'];
            $payment = $task['payment'];
            $mysqli->begin_transaction();
            try {
                $u1 = $mysqli->prepare("UPDATE users SET balance = balance - ? WHERE id=?");
                $u1->bind_param('di',$payment,$rid); $u1->execute();
                $u2 = $mysqli->prepare("UPDATE users SET balance = balance + ? WHERE id=?");
                $u2->bind_param('di',$payment,$worker_id); $u2->execute();
                $tins = $mysqli->prepare("INSERT INTO transactions (user_id,amount,type,note) VALUES (?,?, 'payment', ?)");
                $note = "Payment for task #{$task_id}";
                $tins->bind_param('dds',$rid,$payment,$note); $tins->execute();
                $tins2 = $mysqli->prepare("INSERT INTO transactions (user_id,amount,type,note) VALUES (?,?, 'payout', ?)");
                $tins2->bind_param('dds',$worker_id,$payment,$note); $tins2->execute();
                $apupd = $mysqli->prepare("UPDATE applications SET status='submitted' WHERE id=?");
                $apupd->bind_param('i',$app_id); $apupd->execute();
                $taskupd = $mysqli->prepare("UPDATE tasks SET status='completed' WHERE id=?");
                $taskupd->bind_param('i',$task_id); $taskupd->execute();
                $mysqli->commit();
                echo "<script>alert('Payment approved and worker paid');window.location.href='manage_task.php?id={$task_id}';</script>"; exit;
            } catch (Exception $e) {
                $mysqli->rollback();
                echo "<script>alert('Payment failed');window.location.href='manage_task.php?id={$task_id}';</script>"; exit;
            }
        }
    }
}
 
// fetch applications
$stmt = $mysqli->prepare("SELECT a.*, u.name as worker_name FROM applications a JOIN users u ON a.worker_id=u.id WHERE a.task_id=? ORDER BY a.created_at DESC");
$stmt->bind_param('i',$task_id); $stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Manage Task</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
 body{font-family:Inter;background:#071428;color:#e8fbff;padding:20px}
 .card{background:rgba(255,255,255,0.02);padding:14px;border-radius:10px}
 button{padding:8px;border-radius:8px;border:none;background:#06b6d4;color:#021018;cursor:pointer}
 .muted{color:#9fc0cc}
</style>
</head>
<body>
  <div class="card">
    <h3><?=htmlspecialchars($task['title'])?></h3>
    <div class="muted">Payment $<?=number_format($task['payment'],2)?> · Status: <?=htmlspecialchars($task['status'])?></div>
    <hr>
    <h4>Applications</h4>
    <?php if(count($applications)==0): ?><div class="muted">No applications yet.</div><?php endif; ?>
    <?php foreach($applications as $a): ?>
      <div style="padding:10px;border-radius:8px;background:rgba(255,255,255,0.01);margin-bottom:8px">
        <div style="display:flex;justify-content:space-between">
          <div><strong><?=htmlspecialchars($a['worker_name'])?></strong> — <?=htmlspecialchars($a['status'])?></div>
          <div>
            <?php if($a['status']=='applied'): ?>
              <form style="display:inline" method="post"><input type="hidden" name="app_id" value="<?=$a['id']?>"><input type="hidden" name="action" value="accept"><button type="submit">Accept</button></form>
              <form style="display:inline" method="post"><input type="hidden" name="app_id" value="<?=$a['id']?>"><input type="hidden" name="action" value="reject"><button type="submit">Reject</button></form>
            <?php elseif($a['status']=='submitted'): ?>
              <form method="post" style="display:inline"><input type="hidden" name="app_id" value="<?=$a['id']?>"><input type="hidden" name="action" value="approve_payment"><button type="submit">Approve & Pay</button></form>
            <?php else: ?>
              <span class="muted">No actions</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <div style="margin-top:12px"><button onclick="window.location.href='requester_dashboard.php'">Back</button></div>
  </div>
</body>
</html>
 
