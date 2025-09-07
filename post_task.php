?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'requester') {
    // redirect to login (via JS)
    echo "<script>alert('Login as requester to post tasks');window.location.href='login.php';</script>"; exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $payment = floatval($_POST['payment'] ?? 0);
    $deadline = $_POST['deadline'] ?? null;
    if (!$title || !$description || $payment<=0) { $errors[] = "Title, description and positive payment required."; }
    else {
        $stmt = $mysqli->prepare("INSERT INTO tasks (requester_id,title,description,category,payment,deadline) VALUES (?,?,?,?,?,?)");
        $rid = $_SESSION['user_id'];
        if ($deadline=='') $deadline = null;
        $stmt->bind_param('isssds', $rid, $title, $description, $category, $payment, $deadline);
        if ($stmt->execute()) {
            echo "<script>alert('Task posted');window.location.href='marketplace.php';</script>"; exit;
        } else $errors[] = "DB error";
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Post Task</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:Inter;padding:20px;background:#041027;color:#e8f8ff}
  .wrap{max-width:760px;margin:0 auto;background:linear-gradient(180deg,#05223a,#041025);padding:20px;border-radius:12px}
  input,textarea,select{width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:#eaf8ff}
  label{color:#bcdbe6}
  .row{display:grid;grid-template-columns:1fr 200px;gap:12px}
  .btn{background:linear-gradient(90deg,#06b6d4,#7c3aed);border:none;padding:10px;border-radius:8px;color:#021018;cursor:pointer}
  .err{background:rgba(255,0,0,0.06);padding:8px;border-radius:8px;color:#ffd6d6;margin-bottom:8px}
  @media(max-width:700px){.row{grid-template-columns:1fr}}
</style>
</head>
<body>
  <div class="wrap">
    <h2>Post a new task</h2>
    <?php if($errors): ?><div class="err"><?=htmlspecialchars(implode(' • ',$errors))?></div><?php endif; ?>
    <form method="post" onsubmit="this.querySelector('button').disabled=true;">
      <label>Title</label>
      <input name="title" required>
      <label>Description</label>
      <textarea name="description" rows="6" required></textarea>
      <div class="row" style="margin-top:10px">
        <div>
          <label>Category</label>
          <input name="category" placeholder="e.g. data entry, survey">
        </div>
        <div>
          <label>Payment (USD)</label>
          <input name="payment" type="number" step="0.01" value="1.00" required>
        </div>
      </div>
      <label style="margin-top:10px">Deadline (optional)</label>
      <input name="deadline" type="datetime-local">
      <div style="margin-top:12px;display:flex;gap:8px">
        <button class="btn" type="submit">Post Task</button>
        <button type="button" class="btn" style="background:#243447" onclick="window.location.href='index.php'">Cancel</button>
      </div>
    </form>
  </div>
</body>
</html>
