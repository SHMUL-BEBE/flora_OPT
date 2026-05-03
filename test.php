<?php
session_start();
echo "<h1>Тестовая страница</h1>";
echo "<p>Сайт работает!</p>";

// Проверяем подключение к БД
$conn = new mysqli("localhost", "root", "", "flower");
if ($conn->connect_error) {
    echo "<p style='color:red'>Ошибка БД: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green'>Подключение к БД успешно!</p>";
    
    // Проверяем таблицу klient
    $result = $conn->query("SELECT * FROM klient LIMIT 1");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green'>Таблица klient найдена, есть пользователи</p>";
    } else {
        echo "<p style='color:red'>Таблица klient пуста или не найдена</p>";
    }
    $conn->close();
}

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo '<br><a href="index.php">На главную</a>';
?>