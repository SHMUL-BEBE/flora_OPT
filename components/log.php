<?php
session_start();

$conn = new mysqli("localhost", "root", "", "flower");

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $message = "Заполните все поля";
    } else {
        $stmt = $conn->prepare("SELECT * FROM klient WHERE `e-mail` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && $password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['e-mail'];
            $success = true;
            $message = "Вход выполнен! Перенаправление...";
            echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1000);</script>";
        } else {
            $message = "Неверная почта или пароль";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<div class="form-container">
    <div class="form">
        <div class="menu_profile">
            <p class="re" style="cursor:pointer" onclick="window.location.href='index.php?page=reg'">Регистрация</p>
            <p class="log" style="cursor:pointer" onclick="window.location.href='index.php?page=log'">Войти</p>
        </div>

        <?php if ($message): ?>
            <div style="text-align: center; margin-bottom: 15px; padding: 10px; background: #e6d8c1; border-radius: 10px; color: <?= $success ? 'green' : 'red' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-field">
                <label for="email">Почта</label>
                <input type="email" id="email" name="email" placeholder="Введите почту" required>
            </div>
            
            <div class="form-field">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="Введите пароль" required>
            </div>
            
            <button class="btn2" type="submit" style="background-color: #a37c76;">Войти</button>
        </form>
    </div>
</div>