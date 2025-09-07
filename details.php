?php
require 'db.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("SELECT t.*, u.name requester FROM tasks t JOIN users u ON t.requester_id=u.id WHERE t.id=? LIMIT 1");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$task = $res->fetch_assoc();
if (!$task) { echo "<script>alert('Task not found');window.location.href='marketplace.php';</script>"; exit; }
?>
<!doctype html>
<html><head><meta charset="utf-8"><title><?=htmlspecialchars($task['title'])?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:Inter;background:#081425;color:#e9fbff;padding:20px}
  .wrap{max-width:900px;margin:0 auto;background:rgba(255,255,255,0.02);padding:18px;border-radius:12px}
  .muted{color:#9fc0cc}
  .btn{padding:10px;border-radius:8px;border:none;cursor:pointer;background:#06b6d4;color:#021018}
</style>
</head>
<body>
  <div class="wrap">
    <h2><?=htmlspecialchars($task['title'])?></h2>
    <div class="muted">By <?=htmlspecialchars($task['requester'])?> · <?=htmlspecialchars($task['category'])?></div>
    <div style="margin-top:12px"><?=nl2br(htmlspecialchars($task['description']))?></div>
    <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center">
      <div style="font-weight:700">$<?=number_format($task['payment'],2)?></div>
      <div>
        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role']=='worker'): ?>
          <button class="btn" onclick="window.location.href='apply_task.php?id=<?= $task['id'] ?>'">Apply for Task</button>
        <?php else: ?>
          <button class="btn" onclick="window.location.href='login.php'">Login as Worker to Apply</button>
        <?php endif; ?>
        <button style="margin-left:8px" onclick="window.location.href='marketplace.php'">Back</button>
      </div>
    </div>
  </div>
</body>
</html>
 
