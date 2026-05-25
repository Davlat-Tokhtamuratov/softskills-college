<?php
require_once __DIR__ . '/config/auth.php';
require_login();
$user = current_user();
$isAdmin = (($user['role'] ?? '') === 'admin');

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Ensure required tables exist, so old imports still work.
$pdo->exec("CREATE TABLE IF NOT EXISTS modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  icon VARCHAR(20) DEFAULT '◈',
  color VARCHAR(30) DEFAULT 'accent',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_id INT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT,
  due_date DATE NULL,
  status ENUM('pending','active','done') NOT NULL DEFAULT 'pending',
  priority ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
  assigned_to INT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Seed once if empty.
$countModules = (int)$pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
if ($countModules === 0) {
    $seed = $pdo->prepare("INSERT INTO modules (title, description, icon, color, created_by) VALUES (?,?,?,?,?)");
    $seed->execute(['Командалық жұмыс', 'Топтық жобалар, рөлдер бөлу, жауапкершілік және нәтижеге жету.', '🤝', 'accent', $user['id']]);
    $seed->execute(['Коммуникация', 'Презентация, сөйлеу мәдениеті, конструктивті кері байланыс.', '💬', 'blue', $user['id']]);
    $seed->execute(['Сыни ойлау', 'Кейс-стади, мәселені талдау, дәлелдеу және шешім қабылдау.', '🧠', 'teal', $user['id']]);
    $seed->execute(['Уақыт менеджменті', 'Жоспарлау, дедлайн, flipped classroom және өнімділік.', '⏱', 'amber', $user['id']]);
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add_module') {
            $title = trim($_POST['module_title'] ?? '');
            $desc = trim($_POST['module_description'] ?? '');
            $icon = trim($_POST['module_icon'] ?? '◈');
            $color = $_POST['module_color'] ?? 'accent';
            if ($title === '') throw new Exception('Модуль атауын енгізіңіз.');
            $stmt = $pdo->prepare("INSERT INTO modules (title, description, icon, color, created_by) VALUES (?,?,?,?,?)");
            $stmt->execute([$title, $desc, $icon ?: '◈', $color, $user['id']]);
            $success = 'Жаңа модуль сәтті қосылды.';
        }
        if ($action === 'add_task') {
            $title = trim($_POST['task_title'] ?? '');
            $desc = trim($_POST['task_description'] ?? '');
            $moduleId = !empty($_POST['module_id']) ? (int)$_POST['module_id'] : null;
            $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            $due = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $priority = $_POST['priority'] ?? 'normal';
            if ($title === '') throw new Exception('Тапсырма атауын енгізіңіз.');
            $stmt = $pdo->prepare("INSERT INTO tasks (module_id, title, description, due_date, priority, assigned_to, created_by, status) VALUES (?,?,?,?,?,?,?,'active')");
            $stmt->execute([$moduleId, $title, $desc, $due, $priority, $assignedTo, $user['id']]);
            $success = 'Жаңа тапсырма сәтті қосылды.';
        }
        if ($action === 'delete_task') {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id=?");
            $stmt->execute([(int)($_POST['task_id'] ?? 0)]);
            $success = 'Тапсырма өшірілді.';
        }
        if ($action === 'delete_module') {
            $stmt = $pdo->prepare("DELETE FROM modules WHERE id=?");
            $stmt->execute([(int)($_POST['module_id'] ?? 0)]);
            $success = 'Модуль өшірілді.';
        }
    } catch (Throwable $ex) { $error = $ex->getMessage(); }
}

$modules = $pdo->query("SELECT m.*, COUNT(t.id) AS task_count FROM modules m LEFT JOIN tasks t ON t.module_id=m.id GROUP BY m.id ORDER BY m.id DESC")->fetchAll();
$users = $pdo->query("SELECT id, full_name, username, role FROM users ORDER BY role, full_name")->fetchAll();

if ($isAdmin) {
    $tasksStmt = $pdo->query("SELECT t.*, m.title AS module_title, u.full_name AS assigned_name FROM tasks t LEFT JOIN modules m ON m.id=t.module_id LEFT JOIN users u ON u.id=t.assigned_to ORDER BY t.id DESC");
} else {
    $tasksStmt = $pdo->prepare("SELECT t.*, m.title AS module_title, u.full_name AS assigned_name FROM tasks t LEFT JOIN modules m ON m.id=t.module_id LEFT JOIN users u ON u.id=t.assigned_to WHERE t.assigned_to IS NULL OR t.assigned_to=? ORDER BY t.id DESC");
    $tasksStmt->execute([$user['id']]);
}
$tasks = $tasksStmt->fetchAll();

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalTasks = (int)$pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$totalModules = (int)$pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
$doneTasks = (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status='done'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="kk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>College Soft Skills — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f1117;--bg2:#161922;--bg3:#1e2230;--card:#1a1f2e;--card2:#212638;--border:rgba(255,255,255,.08);--border2:rgba(255,255,255,.14);--text:#eef0f6;--muted:#8b90a7;--accent:#6c63ff;--accent2:#a78bfa;--teal:#2dd4bf;--amber:#f59e0b;--coral:#f87171;--green:#4ade80;--blue:#60a5fa} 
[data-theme="light"]{--bg:#f4f6fb;--bg2:#fff;--bg3:#eef1f7;--card:#fff;--card2:#f7f8fc;--border:rgba(20,25,40,.09);--border2:rgba(20,25,40,.16);--text:#151827;--muted:#667085;--accent:#5b55e7;--accent2:#6c63ff;--teal:#0f9f8f;--amber:#d97706;--coral:#e05252;--green:#16a34a;--blue:#2563eb}
*{box-sizing:border-box;margin:0;padding:0} body{font-family:Manrope,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}.layout{display:flex}.sidebar{width:250px;min-height:100vh;background:var(--bg2);border-right:1px solid var(--border);position:fixed;left:0;top:0;padding:24px 0}.logo{padding:0 22px 22px;border-bottom:1px solid var(--border)}.logo-mark{font-family:'Playfair Display',serif;font-size:22px}.logo-sub{font-size:11px;color:var(--muted);margin-top:4px}.nav-label{font-size:10px;color:var(--muted);letter-spacing:.12em;text-transform:uppercase;padding:18px 22px 8px}.nav-item{display:flex;gap:10px;align-items:center;padding:11px 22px;color:var(--muted);font-size:14px;text-decoration:none;border-left:2px solid transparent}.nav-item.active,.nav-item:hover{color:var(--accent2);background:rgba(108,99,255,.1);border-left-color:var(--accent)}.side-bottom{position:absolute;left:0;right:0;bottom:0;padding:18px 22px;border-top:1px solid var(--border)}.avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--teal));display:flex;align-items:center;justify-content:center;font-weight:800;color:white}.userchip{display:flex;align-items:center;gap:10px}.uname{font-size:13px;font-weight:700}.urole{font-size:11px;color:var(--muted)}.main{margin-left:250px;width:calc(100% - 250px)}.topbar{position:sticky;top:0;z-index:3;background:var(--bg2);border-bottom:1px solid var(--border);padding:18px 32px;display:flex;justify-content:space-between;align-items:center}.top-title{font-weight:800}.top-meta{font-size:13px;color:var(--muted);margin-top:2px}.content{padding:30px 32px}.hero{background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(45,212,191,.07));border:1px solid rgba(108,99,255,.22);border-radius:22px;padding:28px 32px;margin-bottom:22px}.hero-tag{font-size:11px;color:var(--accent2);letter-spacing:.1em;text-transform:uppercase;font-weight:800}.hero h1{font-family:'Playfair Display',serif;font-size:30px;margin:8px 0}.hero p{color:var(--muted);font-size:14px}.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px}.card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:22px;margin-bottom:20px}.stat-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}.stat-val{font-size:34px;font-weight:800;margin-top:6px}.accent{color:var(--accent2)}.teal{color:var(--teal)}.amber{color:var(--amber)}.blue{color:var(--blue)}.card-title{font-size:16px;font-weight:800;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center}.btn{border:none;border-radius:12px;padding:10px 16px;background:var(--card2);color:var(--text);cursor:pointer;font-family:Manrope;font-weight:700;border:1px solid var(--border2);text-decoration:none;display:inline-flex;align-items:center;gap:8px}.btn.primary{background:var(--accent);border-color:var(--accent);color:white}.btn.danger{background:rgba(248,113,113,.12);border-color:rgba(248,113,113,.25);color:var(--coral)}input,select,textarea{width:100%;background:var(--bg3);border:1px solid var(--border2);border-radius:12px;padding:12px 14px;color:var(--text);font-family:Manrope;outline:none}textarea{min-height:92px;resize:vertical}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{margin-bottom:12px}.field label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px}.module-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.module-card{background:var(--bg3);border:1px solid var(--border);border-radius:16px;padding:18px}.mod-head{display:flex;gap:12px;align-items:center;margin-bottom:10px}.mod-icon{width:44px;height:44px;border-radius:13px;background:rgba(108,99,255,.12);display:flex;align-items:center;justify-content:center;font-size:21px}.mod-title{font-weight:800}.mod-desc{font-size:13px;color:var(--muted);line-height:1.55}.chip{font-size:11px;border-radius:999px;padding:4px 9px;background:rgba(108,99,255,.12);color:var(--accent2);font-weight:800}.task{display:flex;gap:14px;padding:14px 0;border-bottom:1px solid var(--border)}.task:last-child{border-bottom:none}.dot{width:20px;height:20px;border-radius:50%;border:2px solid var(--blue);margin-top:2px;flex:0 0 20px}.task-title{font-weight:800;font-size:14px}.task-meta{font-size:12px;color:var(--muted);margin-top:4px;display:flex;gap:8px;flex-wrap:wrap}.alert{padding:12px 16px;border-radius:14px;margin-bottom:18px;font-size:14px}.ok{background:rgba(74,222,128,.12);color:var(--green);border:1px solid rgba(74,222,128,.22)}.err{background:rgba(248,113,113,.12);color:var(--coral);border:1px solid rgba(248,113,113,.22)}.theme-toggle{position:fixed;right:22px;bottom:22px;z-index:99}.table{width:100%;border-collapse:collapse;font-size:13px}.table th,.table td{text-align:left;padding:11px;border-bottom:1px solid var(--border)}.table th{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.08em}@media(max-width:950px){.sidebar{width:70px}.logo-sub,.nav-item span:not(:first-child),.nav-label,.side-bottom div div:not(.avatar){display:none}.main{margin-left:70px;width:calc(100% - 70px)}.grid-4,.grid-2,.module-grid{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
  <div class="logo"><div class="logo-mark">College Soft Skills</div><div class="logo-sub">Информатика · платформа</div></div>
  <div class="nav-label">Негізгі</div>
  <a class="nav-item active" href="dashboard.php"><span>⊞</span><span>Дашборд</span></a>
  <a class="nav-item" href="#modules"><span>◈</span><span>Модульдер</span></a>
  <a class="nav-item" href="#tasks"><span>✦</span><span>Тапсырмалар</span></a>
  <?php if($isAdmin): ?><a class="nav-item" href="#admin"><span>⚙</span><span>Админ басқару</span></a><?php endif; ?>
  <a class="nav-item" href="logout.php"><span>↪</span><span>Шығу</span></a>
  <div class="side-bottom"><div class="userchip"><div class="avatar"><?=e(mb_substr($user['full_name'] ?: $user['username'],0,1))?></div><div><div class="uname"><?=e($user['full_name'] ?: $user['username'])?></div><div class="urole"><?= $isAdmin ? 'Администратор' : 'Пайдаланушы' ?></div></div></div></div>
</aside>
<main class="main">
  <div class="topbar"><div><div class="top-title">Дашборд</div><div class="top-meta"><?=date('d.m.Y')?> · <?= $isAdmin ? 'Админ панель' : 'Жеке кабинет' ?></div></div><button class="btn" onclick="toggleTheme()" id="themeBtn">🌙 Темный фон</button></div>
  <div class="content">
    <?php if($success): ?><div class="alert ok">✅ <?=e($success)?></div><?php endif; ?>
    <?php if($error): ?><div class="alert err">⚠️ <?=e($error)?></div><?php endif; ?>

    <section class="hero">
      <div class="hero-tag"><?= $isAdmin ? 'Admin panel' : 'Student dashboard' ?></div>
      <h1>Сәлем, <?=e($user['full_name'] ?: $user['username'])?>! 👋</h1>
      <p><?= $isAdmin ? 'Бұл беттен жаңа модуль және тапсырма қосып, базадағы мәліметтерді басқара аласыз.' : 'Модульдер мен тапсырмаларды осы жерден көре аласыз.' ?></p>
    </section>

    <div class="grid-4">
      <div class="card"><div class="stat-label">Модульдер</div><div class="stat-val accent"><?=$totalModules?></div></div>
      <div class="card"><div class="stat-label">Тапсырмалар</div><div class="stat-val blue"><?=$totalTasks?></div></div>
      <div class="card"><div class="stat-label">Орындалған</div><div class="stat-val teal"><?=$doneTasks?></div></div>
      <div class="card"><div class="stat-label">Қолданушылар</div><div class="stat-val amber"><?=$totalUsers?></div></div>
    </div>

    <?php if($isAdmin): ?>
    <section id="admin" class="grid-2">
      <div class="card">
        <div class="card-title">➕ Жаңа модуль қосу</div>
        <form method="post">
          <input type="hidden" name="action" value="add_module">
          <div class="form-row"><div class="field"><label>Модуль атауы</label><input name="module_title" placeholder="Мысалы: Көшбасшылық" required></div><div class="field"><label>Иконка</label><input name="module_icon" placeholder="⭐" maxlength="10"></div></div>
          <div class="field"><label>Сипаттамасы</label><textarea name="module_description" placeholder="Модуль туралы қысқаша мәлімет"></textarea></div>
          <div class="field"><label>Түс стилі</label><select name="module_color"><option value="accent">Күлгін</option><option value="blue">Көк</option><option value="teal">Жасыл</option><option value="amber">Сары</option></select></div>
          <button class="btn primary" type="submit">Модульді сақтау</button>
        </form>
      </div>
      <div class="card">
        <div class="card-title">✦ Жаңа тапсырма қосу</div>
        <form method="post">
          <input type="hidden" name="action" value="add_task">
          <div class="field"><label>Тапсырма атауы</label><input name="task_title" placeholder="Мысалы: Топтық жоба дайындау" required></div>
          <div class="form-row"><div class="field"><label>Модуль</label><select name="module_id"><option value="">Модульсіз</option><?php foreach($modules as $m): ?><option value="<?=$m['id']?>"><?=e($m['title'])?></option><?php endforeach; ?></select></div><div class="field"><label>Кімге беріледі?</label><select name="assigned_to"><option value="">Барлық қолданушыға</option><?php foreach($users as $u): if($u['role']==='user'): ?><option value="<?=$u['id']?>"><?=e($u['full_name'])?> (@<?=e($u['username'])?>)</option><?php endif; endforeach; ?></select></div></div>
          <div class="form-row"><div class="field"><label>Дедлайн</label><input type="date" name="due_date"></div><div class="field"><label>Маңыздылығы</label><select name="priority"><option value="normal">Орташа</option><option value="high">Шұғыл</option><option value="low">Төмен</option></select></div></div>
          <div class="field"><label>Сипаттамасы</label><textarea name="task_description" placeholder="Тапсырманың толық сипаттамасы"></textarea></div>
          <button class="btn primary" type="submit">Тапсырманы сақтау</button>
        </form>
      </div>
    </section>
    <?php endif; ?>

    <section id="modules" class="card">
      <div class="card-title"><span>◈ Модульдер</span><span class="chip"><?=count($modules)?> модуль</span></div>
      <div class="module-grid">
        <?php foreach($modules as $m): ?>
        <div class="module-card">
          <div class="mod-head"><div class="mod-icon"><?=e($m['icon'])?></div><div><div class="mod-title"><?=e($m['title'])?></div><span class="chip"><?=$m['task_count']?> тапсырма</span></div></div>
          <div class="mod-desc"><?=nl2br(e($m['description']))?></div>
          <?php if($isAdmin): ?><form method="post" style="margin-top:12px" onsubmit="return confirm('Модульді өшіреміз бе?')"><input type="hidden" name="action" value="delete_module"><input type="hidden" name="module_id" value="<?=$m['id']?>"><button class="btn danger" type="submit">Өшіру</button></form><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="tasks" class="card">
      <div class="card-title"><span>✦ Тапсырмалар</span><span class="chip"><?=count($tasks)?> тапсырма</span></div>
      <?php if(!$tasks): ?><p style="color:var(--muted)">Әзірге тапсырма жоқ.</p><?php endif; ?>
      <?php foreach($tasks as $t): ?>
      <div class="task">
        <div class="dot"></div>
        <div style="flex:1"><div class="task-title"><?=e($t['title'])?></div><div class="task-meta"><span>Модуль: <?=e($t['module_title'] ?: 'Жоқ')?></span><span>Дедлайн: <?=e($t['due_date'] ?: 'көрсетілмеген')?></span><span>Маңыздылығы: <?=e($t['priority'])?></span><span>Кімге: <?=e($t['assigned_name'] ?: 'Барлығына')?></span></div><?php if($t['description']): ?><div class="mod-desc" style="margin-top:8px"><?=nl2br(e($t['description']))?></div><?php endif; ?></div>
        <?php if($isAdmin): ?><form method="post" onsubmit="return confirm('Тапсырманы өшіреміз бе?')"><input type="hidden" name="action" value="delete_task"><input type="hidden" name="task_id" value="<?=$t['id']?>"><button class="btn danger" type="submit">Өшіру</button></form><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </section>

    <?php if($isAdmin): ?>
    <section class="card">
      <div class="card-title">👥 Қолданушылар</div>
      <table class="table"><thead><tr><th>Аты-жөні</th><th>Логин</th><th>Рөлі</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?=e($u['full_name'])?></td><td><?=e($u['username'])?></td><td><?=e($u['role'])?></td></tr><?php endforeach; ?></tbody></table>
    </section>
    <?php endif; ?>
  </div>
</main>
</div>
<script>
function applyTheme(){const t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);const b=document.getElementById('themeBtn');if(b)b.textContent=t==='light'?'🌙 Темный фон':'☀️ Белый фон'}
function toggleTheme(){const now=document.documentElement.getAttribute('data-theme')==='light'?'dark':'light';localStorage.setItem('theme',now);applyTheme()} applyTheme();
</script>
</body>
</html>
