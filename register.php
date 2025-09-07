<?php
require 'db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = ($_POST['role'] === 'requester') ? 'requester' : 'worker';
 
    if (!$name || !$email || !$password) { $errors[] = "All fields are required."; }
 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email."; }
 
    if (empty($errors)) {
        // check exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = $mysqli->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $ins->bind_param('ssss', $name, $email, $hash, $role);
            if ($ins->execute()) {
                // auto-login then redirect via JS
                $_SESSION['user_id'] = $ins->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                echo "<script>localStorage.setItem('flash','Account created.');window.location.href='index.php';</script>";
                exit;
            } else { $errors[] = "DB error."; }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Register</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:Inter,Arial;background:#071025;color:#e9f5fb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .wrap{background:linear-gradient(180deg,#081428,#071025);padding:28px;border-radius:14px;width:420px;box-shadow:0 14px 40px rgba(1,7,20,0.6);border:1px solid rgba(255,255,255,0.03)}
    h2{margin:0 0 14px 0}
    label{display:block;margin-top:10px;font-size:14px;color:#bcd7e6}
    input,select{width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);color:#eaf6ff}
    .actions{margin-top:16px;display:flex;gap:8px}
    .btn{flex:1;padding:10px;border-radius:8px;border:none;cursor:pointer}
    .btn.primary{background:linear-gradient(90deg,#06b6d4,#7c3aed);color:#021018}
    .errors{background:rgba(255,0,0,0.06);padding:8px;border-radius:8px;color:#ffd6d6;margin-bottom:8px}
    .muted{color:#92b7c6;font-size:13px;margin-top:8px}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Create account</h2>
    <?php if(!empty($errors)): ?>
      <div class="errors"><?=htmlspecialchars(implode(' • ', $errors))?></div>
    <?php endif; ?>
    <form method="post" onsubmit="document.getElementById('submitbtn').disabled=true;">
      <label>Name</label>
      <input name="name" required>
      <label>Email</label>
      <input name="email" type="email" required>
      <label>Password</label>
      <input name="password" type="password" minlength="6" required>
      <label>Role</label>
      <select name="role">
        <option value="worker">Worker — complete tasks</option>
        <option value="requester">Requester — post tasks</option>
      </select>
      <div class="actions">
        <button id="submitbtn" class="btn primary" type="submit">Sign up</button>
        <button type="button" class="btn" onclick="window.location.href='login.php'">Have account?</button>
      </div>
      <div class="muted">By signing up you accept terms. This is a demo platform.</div>
    </form>
  </div>
</body>
</html>
