?php
require 'db.php';
session_unset();
session_destroy();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Logged out</title></head><body>
<script>localStorage.setItem('flash','Logged out');window.location.href='index.php';</script>
</body></html>
