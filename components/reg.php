<?php
session_start();

$conn = new mysqli("localhost", "root", "", "flower");

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($fullname) || empty($phone) || empty($email) || empty($password)) {
        $message = "Заполните все поля";
    } else {
        $check = $conn->prepare("SELECT id FROM klient WHERE `e-mail` = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $message = "Пользователь с такой почтой уже существует";
        } else {
            $sql = "INSERT INTO klient (name, phone, `e-mail`, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $fullname, $phone, $email, $password);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $fullname;
                $_SESSION['user_email'] = $email;
                $success = true;
                $message = "Регистрация успешна! Перенаправление...";
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1000);</script>";
            } else {
                $message = "Ошибка: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
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
            
            <button class="btn1" type="submit" style="background-color: #8e9680;">Зарегистрироваться</button>
        </form>
    </div>
</div>