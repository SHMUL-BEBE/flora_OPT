<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=auth');
    exit;
}

$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>

<div class="profile-container">
    <h2 class="profile-title">Мой профиль</h2>
    
    <div class="profile-info">
        <div class="profile-field">
            <span class="profile-label">Имя:</span>
            <span class="profile-value"><?php echo htmlspecialchars($userName); ?></span>
        </div>
        <div class="profile-field">
            <span class="profile-label">Email:</span>
            <span class="profile-value"><?php echo htmlspecialchars($userEmail); ?></span>
        </div>
        <div class="profile-field">
            <span class="profile-label">Роль:</span>
            <span class="profile-value">
                <?php 
                if ($userRole == 'админ') {
                    echo 'Администратор';
                } elseif ($userRole == 'сотрудник') {
                    echo 'Сотрудник';
                } else {
                    echo 'Клиент';
                }
                ?>
            </span>
        </div>
    </div>
    
    <div class="profile-buttons">
        <?php if ($userRole == 'админ' || $userRole == 'сотрудник'): ?>
            <button class="btn1" onclick="window.location.href='index.php?page=admin'">Перейти в админ-панель</button>
        <?php endif; ?>
        <button class="btn2" onclick="logout()">Выйти из аккаунта</button>
    </div>
</div>

<script>
function logout() {
    if (confirm('Вы уверены, что хотите выйти?')) {
        fetch('components/trash.php?logout=1')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.href = 'index.php';
                }
            })
            .catch(function(error) {
                console.error('Ошибка:', error);
                window.location.href = 'index.php';
            });
    }
}
</script>