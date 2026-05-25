# softskills-college
SoftSkill Lab PHP + MySQL нұсқасы

Орнату:
1) XAMPP/WAMP іске қосыңыз: Apache және MySQL.
2) softskill_auth папкасын htdocs ішіне салыңыз.
3) phpMyAdmin ашып, database.sql файлын импорт жасаңыз.
4) config/database.php ішінде MySQL логин/парольді тексеріңіз.
5) Браузерден ашыңыз: http://localhost/softskill_auth/

Демо аккаунттар:
Admin: admin / admin123
User: user / user123

Файлдар:
- login.php — авторизация
- register.php — регистрация
- logout.php — шығу
- dashboard.php — негізгі платформа, тек кірген қолданушыға ашылады
- admin/index.php — тек admin кіретін панель
- database.sql — база құрылымы
- config/database.php — базаға қосылу
- config/auth.php — session және роль тексеру


ТҮЗЕТУ ЕСКЕРТПЕСІ:
Егер admin / admin123 кірмесе, себебі бұрын базаға ескі пароль хеші сақталып қалған болуы мүмкін.
Шешімі: phpMyAdmin -> softskill_lab базасы -> Import -> reset_admin_password.sql файлын импорттаңыз.
Содан кейін login.php арқылы кіріңіз:
Admin: admin / admin123
User: user / user123

Тексеру үшін браузерден check_login_test.php ашуға болады. Екеуі де OK деп шығуы керек.


Тема ауыстыру:
- Барлық негізгі беттерде төменгі оң жақта батырма бар.
- 🌙 Темный фон / ☀️ Белый фон режимдері localStorage арқылы сақталады.
