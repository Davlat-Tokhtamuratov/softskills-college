<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();
$stmt = $pdo->query('SELECT id, full_name, username, email, role, created_at FROM users ORDER BY id DESC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html><html lang="kk"><head>
<script>
(function(){
  var saved = localStorage.getItem('site-theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Админка</title>
<style>body{margin:0;background:#0f1117;color:#eef0f6;font-family:Arial,sans-serif}.wrap{max-width:1100px;margin:0 auto;padding:32px}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}a.btn{display:inline-block;padding:10px 14px;border-radius:10px;background:#6c63ff;color:#fff;text-decoration:none;margin-left:8px}.card{background:#1a1f2e;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:20px}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid rgba(255,255,255,.08)}th{color:#a78bfa}.role{padding:4px 9px;border-radius:20px;background:#1e2230}.admin{color:#f59e0b}.user{color:#4ade80}
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

</style></head><body><div class="wrap"><div class="top"><div><h1>Админ панель</h1><p>Пайдаланушылар тізімі</p></div><div><a class="btn" href="../dashboard.php">Платформа</a><a class="btn" href="../logout.php">Шығу</a></div></div><div class="card"><table><thead><tr><th>ID</th><th>Аты-жөні</th><th>Username</th><th>Email</th><th>Role</th><th>Тіркелген күні</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?=$u['id']?></td><td><?=htmlspecialchars($u['full_name'])?></td><td><?=htmlspecialchars($u['username'])?></td><td><?=htmlspecialchars($u['email'])?></td><td><span class="role <?=$u['role']==='admin'?'admin':'user'?>"><?=htmlspecialchars($u['role'])?></span></td><td><?=htmlspecialchars($u['created_at'])?></td></tr><?php endforeach; ?></tbody></table></div></div>
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
