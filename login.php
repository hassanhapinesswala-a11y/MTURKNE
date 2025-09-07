<?php
require 'db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) { $err = "Provide email and password."; }
    else {
        $stmt = $mysqli->prepare("SELECT id,name,password,role FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s',$email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id']=$row['id'];
                $_SESSION['user_name']=$row['name'];
                $_SESSION['user_role']=$row['role'];
                echo "<script>localStorage.setItem('flash','Welcome back');window.location.href='index.php';</script>"; exit;
            } else { $err = "Invalid credentials."; }
        } else { $err = "Invalid credentials."; }
    }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><title>Login</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:Inter,Arial;background:linear-gradient(180deg,#041024,#031426);color:#e8f7ff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
  .box{width:380px;background:rgba(255,255,255,0.02);padding:22px;border-radius:12px;box-shadow:0 10px 30px rgba(2,8,24,0.6)}
  h2{margin:0 0 12px 0}
  input{width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:#eaffff}
  .btn{margin-top:12px;width:100%;padding:10px;border-radius:8px;border:none;background:#06b6d4;color:#021018;cursor:pointer}
  .err{background:rgba(255,0,0,0.06);padding:8px;border-radius:8px;color:#ffd6d6;margin-bottom:8px}
</style>
</head>
<body>
  <div class="box">
    <h2>Login</h2>
    <?php if($err): ?><div class="err"><?=htmlspecialchars($err)?></div><?php endif; ?>
    <form method="post" onsubmit="this.querySelector('button').disabled=true;">
      <input name="email" type="email" placeholder="Email" required>
      <input name="password" type="password" placeholder="Password" required>
      <button class="btn" type="submit">Login</button>
    </form>
    <div style="margin-top:10px;color:#9fc3d6">No account? <a style="color:#e6faff" href="register.php">Sign up</a></div>
  </div>
</body>
</html>
