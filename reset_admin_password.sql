-- Егер база бұрын импортталған болса, осы файлды phpMyAdmin арқылы Import жасаңыз
-- Admin логин/пароль: admin / admin123
-- User логин/пароль: user / user123

USE softskill_lab;

UPDATE users SET password = '$2y$12$Vm.0OH7RuavS6nNqvNRRe.EcFQbIIIQKYQidpu/odwXCrYkLo/xDu', role = 'admin', full_name='Администратор', email='admin@softskill.local' WHERE username = 'admin';
INSERT INTO users (full_name, username, email, password, role)
SELECT 'Администратор', 'admin', 'admin@softskill.local', '$2y$12$Vm.0OH7RuavS6nNqvNRRe.EcFQbIIIQKYQidpu/odwXCrYkLo/xDu', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');

UPDATE users SET password = '$2y$12$w9jK71e7NSdcgw3qga23i.QAoe9si7Dnkq72uEWmgLZ8IJl425yEW', role = 'user', full_name='Demo User', email='user@softskill.local' WHERE username = 'user';
INSERT INTO users (full_name, username, email, password, role)
SELECT 'Demo User', 'user', 'user@softskill.local', '$2y$12$w9jK71e7NSdcgw3qga23i.QAoe9si7Dnkq72uEWmgLZ8IJl425yEW', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='user');
