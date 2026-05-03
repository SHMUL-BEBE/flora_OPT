<?php
ob_start(); // Включаем буферизацию вывода
session_start();

// Определяем показ корзины
$showCart = true;
if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'админ' || $_SESSION['user_role'] == 'сотрудник')) {
    $showCart = false;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'main';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Флора ОПТ</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="icon" href="img/logo1.svg" type="image/x-icon" />
</head>

<body>
    
    <header>
        <p class="name_logo">Флора ОПТ</p>
        <img class="head_logo" id="logoBtn" src="img/logo1.svg" alt="логотип" style="cursor: pointer;" />
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="header-user-info">
                <span class="user-name" onclick="window.location.href='index.php?page=profile'" style="cursor: pointer;">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <?php 
                if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'админ' || $_SESSION['user_role'] == 'сотрудник')): 
                    $btnText = ($_SESSION['user_role'] == 'админ') ? 'Админ' : 'Сотрудник';
                ?>
                    <button class="admin-btn" onclick="window.location.href='index.php?page=admin'"><?php echo $btnText; ?></button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <img class="profile" id="profileBtn" src="img/profile.svg" alt="профиль" style="cursor: pointer;" />
        <?php endif; ?>
        
        <?php if ($showCart): ?>
            <img class="trash" id="trashBtn" src="img/trash.svg" alt="корзина" style="cursor: pointer;" />
        <?php endif; ?>
    </header>
    
    <main>
        <?php
        switch($page) {
            case 'auth':
                include 'components/auth.php';
                break;
            case 'trash':
                include 'components/trash.php';
                break;
            case 'admin':
                include 'components/admin.php';
                break;
            case 'profile':
                include 'components/profile.php';
                break;
            default:
                include 'components/main.php';
        }
        ?>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var logoBtn = document.getElementById('logoBtn');
        var trashBtn = document.getElementById('trashBtn');
        
        if (logoBtn) {
            logoBtn.addEventListener('click', function() {
                window.location.href = 'index.php';
            });
        }
        
        <?php if (!isset($_SESSION['user_id'])): ?>
        var profileBtn = document.getElementById('profileBtn');
        if (profileBtn) {
            profileBtn.addEventListener('click', function() {
                window.location.href = 'index.php?page=auth';
            });
        }
        <?php endif; ?>
        
        if (trashBtn) {
            trashBtn.addEventListener('click', function() {
                window.location.href = 'index.php?page=trash';
            });
        }
    });
    
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
    
    function addToCart(id, name, price) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            if (confirm('Для добавления в корзину необходимо авторизоваться. Перейти на страницу входа?')) {
                window.location.href = 'index.php?page=auth';
            }
            return;
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'админ' || $_SESSION['user_role'] == 'сотрудник')): ?>
            alert('Сотрудники и администраторы не могут добавлять товары в корзину');
            return;
        <?php endif; ?>
        
        fetch('components/trash.php?add=1', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, name: name, price: price })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Товар добавлен в корзину!');
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(function(error) { 
            console.error('Ошибка:', error); 
            alert('Произошла ошибка при добавлении товара');
        });
    }
    </script>
</body>

</html>
<?php
ob_end_flush(); // В конце файла отключаем буферизацию
?>