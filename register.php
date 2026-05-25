<?php
session_start();
require_once __DIR__ . '/config/database.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $error = 'Парольдер бірдей емес!';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль кемінде 6 символ болсын!';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users(full_name, username, email, password, role) VALUES(?,?,?,?,"user")');
            $stmt->execute([$full_name, $username, $email, $hash]);
            $success = 'Тіркелу сәтті өтті. Енді кіре аласыз.';
        } catch (PDOException $e) {
            $error = 'Username немесе email бұрын тіркелген.';
        }
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
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Тіркелу</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0f1117;color:#eef0f6;font-family:Arial,sans-serif}.box{width:420px;max-width:92%;background:#1a1f2e;border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:28px}h1{margin:0 0 8px}.muted{color:#8b90a7;margin-bottom:18px}input{width:100%;box-sizing:border-box;margin:7px 0 12px;padding:13px;border-radius:12px;border:1px solid rgba(255,255,255,.14);background:#1e2230;color:#fff}button{width:100%;padding:13px;border:0;border-radius:12px;background:#6c63ff;color:#fff;font-weight:700}.err{background:rgba(248,113,113,.12);color:#f87171;padding:10px;border-radius:10px}.ok{background:rgba(74,222,128,.12);color:#4ade80;padding:10px;border-radius:10px}.link{text-align:center;margin-top:14px}.link a{color:#a78bfa;text-decoration:none}
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

</style></head><body><form class="box" method="post"><h1>Тіркелу</h1><div class="muted">Жаңа user аккаунт жасау</div><?php if($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?><?php if($success): ?><div class="ok"><?=htmlspecialchars($success)?></div><?php endif; ?><input name="full_name" placeholder="Аты-жөні" required><input name="username" placeholder="Username" required><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Пароль" required><input type="password" name="password2" placeholder="Парольді қайталаңыз" required><button>Тіркелу</button><div class="link"><a href="login.php">Кіру бетіне қайту</a></div></form>
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
