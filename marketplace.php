?php
require 'db.php';
$category = $_GET['category'] ?? null;
$q = "SELECT t.id,t.title,t.payment,t.category,t.status,t.deadline,u.name requester FROM tasks t JOIN users u ON t.requester_id=u.id";
$params = [];
if ($category) { $q .= " WHERE t.category=?"; $params[] = $category; }
$q .= " ORDER BY t.created_at DESC";
$stmt = $mysqli->prepare($q);
if ($category) $stmt->bind_param('s',$category);
$stmt->execute();
$res = $stmt->get_result();
$tasks = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Marketplace</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:Inter;background:#071022;color:#dff5ff;margin:0;padding:20px}
  .wrap{max-width:1100px;margin:0 auto}
  header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
  .task{background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);padding:14px;border-radius:10px;border:1px solid rgba(255,255,255,0.03)}
  .muted{color:#92b7c6;font-size:13px}
  @media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:600px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
  <div class="wrap">
    <header>
      <div>
        <h2 style="margin:0">Task Marketplace</h2>
        <div class="muted">Browse and apply to tasks</div>
      </div>
      <div>
        <button onclick="window.location.href='post_task.php'">Post Task</button>
        <button onclick="window.location.href='index.php'">Home</button>
      </div>
    </header>
 
    <div style="margin-bottom:12px">
      <strong>Filter:</strong>
      <a href="marketplace.php" class="muted">All</a> |
      <a href="marketplace.php?category=data entry" class="muted">Data entry</a> |
      <a href="marketplace.php?category=survey" class="muted">Survey</a> |
      <a href="marketplace.php?category=transcription" class="muted">Transcription</a>
    </div>
 
    <div class="grid">
      <?php foreach($tasks as $t): ?>
        <div class="task">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div style="font-weight:700"><?=htmlspecialchars($t['title'])?></div>
              <div class="muted">By <?=htmlspecialchars($t['requester'])?> · <?=htmlspecialchars($t['category'])?></div>
            </div>
            <div style="text-align:right">
              <div style="font-weight:700">$<?=number_format($t['payment'],2)?></div>
              <div class="muted"><?= $t['deadline'] ? date('M j', strtotime($t['deadline'])) : '' ?></div>
            </div>
          </div>
          <div style="margin-top:12px;display:flex;gap:8px">
            <button onclick="window.location.href='task_details.php?id=<?= $t['id'] ?>'">View</button>
            <button onclick="window.location.href='apply_task.php?id=<?= $t['id'] ?>'">Apply</button>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if(count($tasks)==0): ?><div class="task">No tasks found.</div><?php endif; ?>
    </div>
  </div>
</body>
</html>
