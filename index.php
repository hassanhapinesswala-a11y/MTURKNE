<?php
// index.php
require 'db.php';
 
// fetch featured tasks (latest 6 open)
$stmt = $mysqli->prepare("SELECT t.id, t.title, t.payment, t.deadline, u.name as requester FROM tasks t JOIN users u ON t.requester_id=u.id WHERE t.status='open' ORDER BY t.created_at DESC LIMIT 6");
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>MTURKCOMCLONE — Home</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* Internal — stylish modern card layout */
    :root{--accent:#06b6d4;--dark:#0f172a;--muted:#6b7280}
    *{box-sizing:border-box;font-family:Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial}
    body{margin:0;background:linear-gradient(180deg,#071430 0%, #071a2b 60%);color:#e6eef8;min-height:100vh}
    header{display:flex;justify-content:space-between;align-items:center;padding:18px 32px}
    .brand{display:flex;gap:14px;align-items:center}
    .logo{width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700}
    .cta{display:flex;gap:10px}
    .btn{background:transparent;color:#e6eef8;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);cursor:pointer}
    .btn.primary{background:var(--accent);color:#042027;border:none;box-shadow:0 6px 18px rgba(3,105,121,0.15)}
    main{padding:32px;max-width:1100px;margin:0 auto}
    .hero{display:grid;grid-template-columns:1fr 420px;gap:28px;align-items:center;margin-bottom:26px}
    .card{background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(255,255,255,0.02));padding:20px;border-radius:14px;box-shadow:0 8px 30px rgba(2,6,23,0.6);border:1px solid rgba(255,255,255,0.03)}
    .tasks-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    .task{padding:14px;border-radius:10px;background:linear-gradient(180deg,rgba(255,255,255,0.015),transparent);border:1px solid rgba(255,255,255,0.02)}
    .muted{color:var(--muted);font-size:13px}
    footer{padding:24px;text-align:center;color:var(--muted)}
    @media(max-width:900px){.hero{grid-template-columns:1fr;}.tasks-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:480px){.tasks-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <div class="logo">MC</div>
      <div>
        <div style="font-weight:700">MTURKCOMCLONE</div>
        <div class="muted" style="font-size:13px">Micro-task marketplace</div>
      </div>
    </div>
    <div class="cta">
      <?php if(isset($_SESSION['user_id'])): ?>
        <button class="btn" onclick="window.location.href='logout.php'">Logout</button>
        <button class="btn primary" onclick="window.location.href='marketplace.php'">Marketplace</button>
      <?php else: ?>
        <button class="btn" onclick="window.location.href='login.php'">Login</button>
        <button class="btn primary" onclick="window.location.href='register.php'">Sign up</button>
      <?php endif; ?>
    </div>
  </header>
 
  <main>
    <div class="hero">
      <div class="card">
        <h1 style="margin:0 0 8px 0">Earn by completing micro-tasks</h1>
        <p class="muted">Sign up as a worker to browse tasks like data entry, surveys, transcription. Requesters can post tasks and pay workers directly.</p>
        <div style="margin-top:18px;display:flex;gap:10px">
          <button class="btn primary" onclick="window.location.href='marketplace.php'">Browse Tasks</button>
          <button class="btn" onclick="window.location.href='post_task.php'">Post a Task</button>
        </div>
        <hr style="margin:18px 0;border:none;border-top:1px solid rgba(255,255,255,0.03)">
        <div style="display:flex;gap:12px;align-items:center">
          <div style="background:rgba(255,255,255,0.03);padding:8px 12px;border-radius:8px">Safe payments</div>
          <div style="background:rgba(255,255,255,0.03);padding:8px 12px;border-radius:8px">Fast reviews</div>
          <div style="background:rgba(255,255,255,0.03);padding:8px 12px;border-radius:8px">Ratings & feedback</div>
        </div>
      </div>
 
      <div class="card">
        <h3 style="margin-top:0">Featured Tasks</h3>
        <?php if(count($tasks)==0): ?>
          <p class="muted">No tasks available. Requesters — post the first task!</p>
        <?php else: ?>
          <?php foreach($tasks as $t): ?>
            <div style="margin-bottom:12px;">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <div>
                  <div style="font-weight:600"><?=htmlspecialchars($t['title'])?></div>
                  <div class="muted">By <?=htmlspecialchars($t['requester'])?></div>
                </div>
                <div style="text-align:right">
                  <div style="font-weight:700">$<?=number_format($t['payment'],2)?></div>
                  <div class="muted" style="font-size:12px"><?= $t['deadline'] ? date('M j, Y', strtotime($t['deadline'])) : 'No deadline' ?></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
 
    <section class="card">
      <h3>How it works</h3>
      <ol class="muted">
        <li>Sign up as Worker or Requester.</li>
        <li>Requesters post tasks with payment.</li>
        <li>Workers apply, submit, and get paid after approval.</li>
      </ol>
    </section>
  </main>
 
  <footer>
    MTURKCOMCLONE — built with PHP + MySQL • Use JS for redirection
  </footer>
</body>
</html>
 
