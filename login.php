<?php
session_start();
require_once __DIR__ . '/config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php'));
        exit;
    } else {
        $error = 'Логин немесе пароль қате!';
    }
}
?>
<!DOCTYPE html><html lang="kk"><head>
<script>
(function(){
  var saved = localStorage.getItem('site-theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Кіру</title>
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0f1117;color:#eef0f6;font-family:Arial,sans-serif}.box{width:380px;max-width:92%;background:#1a1f2e;border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.35)}h1{margin:0 0 8px;font-size:28px}.muted{color:#8b90a7;margin-bottom:22px}input{width:100%;box-sizing:border-box;margin:8px 0 14px;padding:13px 14px;border-radius:12px;border:1px solid rgba(255,255,255,.14);background:#1e2230;color:#fff}button{width:100%;padding:13px;border:0;border-radius:12px;background:#6c63ff;color:#fff;font-weight:700;cursor:pointer}.err{background:rgba(248,113,113,.12);color:#f87171;padding:10px;border-radius:10px;margin-bottom:12px}.link{margin-top:16px;text-align:center;color:#8b90a7}.link a{color:#a78bfa;text-decoration:none}.demo{font-size:12px;color:#8b90a7;line-height:1.6;margin-top:16px;background:#161922;padding:12px;border-radius:10px}

/* THEME TOGGLE: dark / light */
html[data-theme="light"] {
  --bg: #f4f6fb;
  --bg2: #ffffff;
  --bg3: #edf1f8;
  --card: #ffffff;
  --card2: #f1f5fb;
  --border: rgba(15, 23, 42, 0.10);
  --border2: rgba(15, 23, 42, 0.18);
  --text: #111827;
  --muted: #64748b;
  --accent: #5b55e6;
  --accent2: #6d5dfc;
  --teal: #0f766e;
  --amber: #d97706;
  --coral: #dc2626;
  --green: #16a34a;
  --blue: #2563eb;
  --glow: rgba(91,85,230,0.12);
}
html[data-theme="light"] body { background: var(--bg); color: var(--text); }
html[data-theme="light"] .box,
html[data-theme="light"] .card { background: #ffffff !important; color: #111827 !important; border-color: rgba(15,23,42,.12) !important; box-shadow: 0 16px 45px rgba(15,23,42,.10); }
html[data-theme="light"] input,
html[data-theme="light"] textarea { background: #f8fafc !important; color: #111827 !important; border-color: rgba(15,23,42,.18) !important; }
html[data-theme="light"] input::placeholder,
html[data-theme="light"] textarea::placeholder { color: #64748b !important; }
html[data-theme="light"] .muted,
html[data-theme="light"] .demo,
html[data-theme="light"] p { color: #64748b !important; }
html[data-theme="light"] .demo,
html[data-theme="light"] .role { background: #f1f5f9 !important; }
html[data-theme="light"] th { color: #5b55e6 !important; }
html[data-theme="light"] th,
html[data-theme="light"] td { border-bottom-color: rgba(15,23,42,.10) !important; }
.theme-toggle-btn{
  position:fixed;right:20px;bottom:20px;z-index:9999;
  width:auto !important;padding:11px 15px !important;border-radius:999px !important;
  border:1px solid var(--border2, rgba(255,255,255,.16)) !important;
  background:var(--card2, #212638) !important;color:var(--text, #eef0f6) !important;
  font-weight:700 !important;font-size:13px !important;cursor:pointer !important;
  box-shadow:0 10px 30px rgba(0,0,0,.22) !important;
}
html[data-theme="light"] .theme-toggle-btn{box-shadow:0 10px 30px rgba(15,23,42,.16) !important;}

</style></head><body><form class="box" method="post"><h1>SoftSkill Lab</h1><div class="muted">Аккаунтқа кіру</div><?php if($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?><input name="login" placeholder="Username немесе Email" required><input type="password" name="password" placeholder="Пароль" required><button>Кіру</button><div class="link">Аккаунт жоқ па? <a href="register.php">Тіркелу</a></div><div class="demo">Demo:<br>Admin: <b>admin</b> / <b>admin123</b><br>User: <b>user</b> / <b>user123</b></div></form>
<button type="button" class="theme-toggle-btn" id="themeToggleBtn">🌙 Темный фон</button>
<script>
(function(){
  function applyTheme(theme){
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('site-theme', theme);
    var btn = document.getElementById('themeToggleBtn');
    if(btn){ btn.textContent = theme === 'light' ? '☀️ Белый фон' : '🌙 Темный фон'; }
  }
  var current = localStorage.getItem('site-theme') || document.documentElement.getAttribute('data-theme') || 'dark';
  applyTheme(current);
  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'themeToggleBtn'){
      var now = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      applyTheme(now);
    }
  });
})();
</script>

</body></html>
