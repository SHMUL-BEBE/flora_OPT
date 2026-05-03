<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
$success = false;
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Регистрация
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($fullname) || empty($phone) || empty($email) || empty($password)) {
        $message = "Заполните все поля";
    } else {
        // Проверяем в таблице klient
        $stmt = $pdo->prepare("SELECT id FROM klient WHERE `e-mail` = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = "Пользователь с такой почтой уже существует";
        } else {
            $stmt = $pdo->prepare("INSERT INTO klient (name, phone, `e-mail`, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$fullname, $phone, $email, $password])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $fullname;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'client';
                $_SESSION['user_type'] = 'klient';
                $success = true;
                $message = "Регистрация успешна! Перенаправление...";
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1000);</script>";
            } else {
                $message = "Ошибка регистрации";
            }
        }
    }
}

// Вход
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $message = "Заполните все поля";
    } else {
        // Сначала проверяем в таблице users (сотрудники и админы)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE `e-mail` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['e-mail'];
            $_SESSION['user_role'] = $user['status'];
            $_SESSION['user_type'] = 'users';
            $success = true;
            $message = "Вход выполнен! Перенаправление...";
            echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1000);</script>";
        } else {
            // Проверяем в таблице klient
            $stmt = $pdo->prepare("SELECT * FROM klient WHERE `e-mail` = ?");
            $stmt->execute([$email]);
            $client = $stmt->fetch();
            
            if ($client && $password == $client['password']) {
                $_SESSION['user_id'] = $client['id'];
                $_SESSION['user_name'] = $client['name'];
                $_SESSION['user_email'] = $client['e-mail'];
                $_SESSION['user_role'] = 'client';
                $_SESSION['user_type'] = 'klient';
                $success = true;
                $message = "Вход выполнен! Перенаправление...";
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1000);</script>";
            } else {
                $message = "Неверная почта или пароль";
            }
        }
    }
}
?>

<div class="form-container">
    <div class="form">
        <div class="menu_profile">
            <p class="<?php echo $activeTab == 'login' ? 'log' : 're'; ?>" onclick="window.location.href='index.php?page=auth&tab=login'">Войти</p>
            <p class="<?php echo $activeTab == 'register' ? 're' : 'log'; ?>" onclick="window.location.href='index.php?page=auth&tab=register'">Регистрация</p>
        </div>

        <?php if ($message != ''): ?>
            <div class="auth-message <?php echo $success ? 'auth-success' : 'auth-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($activeTab == 'login'): ?>
        <form method="POST" action="">
            <div class="form-field">
                <label for="email">Почта</label>
                <input type="email" id="email" name="email" placeholder="Введите почту" required>
            </div>
            
            <div class="form-field">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="Введите пароль" required>
            </div>
            
            <button class="btn2" type="submit" name="login">Войти</button>
        </form>
        <?php else: ?>
        <form method="POST" action="">
            <div class="form-field">
                <label for="fullname">ФИО</label>
                <input type="text" id="fullname" name="fullname" placeholder="Введите ФИО" required>
            </div>
            
            <div class="form-field">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" placeholder="Введите телефон" required>
            </div>
            
            <div class="form-field">
                <label for="email">Почта</label>
                <input type="email" id="email" name="email" placeholder="Введите почту" required>
            </div>
            
            <div class="form-field">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="Введите пароль" required>
            </div>
            
            <button class="btn1" type="submit" name="register">Зарегистрироваться</button>
        </form>
        <?php endif; ?>
    </div>
</div>