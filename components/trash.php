<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Выход из аккаунта
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(array('success' => true));
    exit;
}

// Проверка - сотрудники и админы перенаправляются в профиль
if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'админ' || $_SESSION['user_role'] == 'сотрудник')) {
    header('Location: index.php?page=profile');
    exit;
}

// Добавление товара в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['add'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(array('success' => false, 'message' => 'Необходимо авторизоваться'));
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $productId = $data['id'];
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $_SESSION['cart'][$productId] = array(
            'name' => $data['name'],
            'price' => $data['price'],
            'quantity' => 1
        );
    }
    
    echo json_encode(array('success' => true));
    exit;
}

// Изменение количества товара
if (isset($_GET['change_quantity'])) {
    header('Content-Type: application/json');
    $id = $_GET['change_quantity'];
    $action = $_GET['action'];
    
    if (isset($_SESSION['cart'][$id])) {
        if ($action == 'increase') {
            $_SESSION['cart'][$id]['quantity']++;
        } elseif ($action == 'decrease') {
            $_SESSION['cart'][$id]['quantity']--;
            if ($_SESSION['cart'][$id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
    }
    echo json_encode(array('success' => true));
    exit;
}

// Удаление товара
if (isset($_GET['remove'])) {
    header('Content-Type: application/json');
    $id = $_GET['remove'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    echo json_encode(array('success' => true));
    exit;
}

// Очистка корзины
if (isset($_GET['clear'])) {
    header('Content-Type: application/json');
    $_SESSION['cart'] = array();
    echo json_encode(array('success' => true));
    exit;
}

// Если не авторизован - показываем форму входа
if (!isset($_SESSION['user_id'])) {
    ?>
    <div class="cart-container">
        <h2 class="cart-title">Корзина</h2>
        <div class="cart-empty">
            <div class="cart-empty-title">Для работы с корзиной необходимо авторизоваться</div>
            <button class="btn1" onclick="window.location.href='index.php?page=auth'">Войти</button>
            <div class="cart-empty-text">Нет аккаунта? <a href="index.php?page=auth&tab=register" class="cart-link">Зарегистрируйтесь</a></div>
        </div>
    </div>
    <?php
    exit;
}

$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<div class="cart-page">
    <h2 class="cart-title">Корзина</h2>
    
    <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <div class="empty-cart-title">Корзина пуста</div>
            <div class="empty-cart-text">Добавьте товары из каталога</div>
            <button class="btn1" onclick="window.location.href='index.php'">Перейти в каталог</button>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart as $id => $item): 
                $itemTotal = $item['price'] * $item['quantity'];
            ?>
                <div class="cart-item" data-id="<?php echo $id; ?>">
                    <div class="cart-item-info">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="cart-item-price"><?php echo number_format($item['price'], 0, ',', ' '); ?> ₽ · шт</div>
                        <div class="cart-item-stock">ассортимент · в наличии</div>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="changeQuantity(<?php echo $id; ?>, 'decrease')">−</button>
                            <span class="quantity-value" id="qty-<?php echo $id; ?>"><?php echo $item['quantity']; ?></span>
                            <button class="quantity-btn" onclick="changeQuantity(<?php echo $id; ?>, 'increase')">+</button>
                        </div>
                        <div class="cart-item-total">
                            <div class="cart-item-total-price" id="total-<?php echo $id; ?>">
                                <?php echo number_format($itemTotal, 0, ',', ' '); ?> ₽
                            </div>
                            <button class="remove-item" onclick="removeFromCart(<?php echo $id; ?>)">Удалить</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <div class="cart-total-label">Итого:</div>
            <div class="cart-total-amount" id="cart-total"><?php echo number_format($total, 0, ',', ' '); ?> ₽</div>
        </div>
        
        <div class="cart-buttons">
            <button class="btn1" onclick="clearCart()">Очистить корзину</button>
            <button class="btn2" onclick="createOrder()">Заказать</button>
        </div>
    <?php endif; ?>
</div>

<script>
function changeQuantity(id, action) {
    fetch('components/trash.php?change_quantity=' + id + '&action=' + action)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = 'index.php?page=trash';
            }
        })
        .catch(function(error) { console.error('Ошибка:', error); });
}

function removeFromCart(id) {
    if (confirm('Удалить товар из корзины?')) {
        fetch('components/trash.php?remove=' + id)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.href = 'index.php?page=trash';
                }
            })
            .catch(function(error) { console.error('Ошибка:', error); });
    }
}

function clearCart() {
    if (confirm('Очистить всю корзину?')) {
        fetch('components/trash.php?clear=1')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.href = 'index.php?page=trash';
                }
            })
            .catch(function(error) { console.error('Ошибка:', error); });
    }
}

function createOrder() {
    if (confirm('Оформить заказ?')) {
        alert('Заказ оформлен! Спасибо за покупку!');
        clearCart();
    }
}

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