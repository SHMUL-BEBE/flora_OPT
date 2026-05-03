<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Проверка прав доступа
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}

$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
if ($userRole != 'админ' && $userRole != 'сотрудник') {
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}

$isAdmin = ($userRole == 'админ');
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : ($isAdmin ? 'products' : 'orders');
$message = '';
$editProduct = null;

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Сохранение товара (только для админа)
    if ($isAdmin && isset($_POST['save_product'])) {
        $id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $category = $_POST['category'];
        $name = trim($_POST['name']);
        $foto = trim($_POST['foto']);
        $kolvo = (int)$_POST['kolvo'];
        $cena = (int)$_POST['cena'];
        $skidka = isset($_POST['skidka']) ? 1 : 0;
        $old_foto = isset($_POST['old_foto']) ? trim($_POST['old_foto']) : '';

        try {
            if ($id > 0 && !empty($old_foto) && $foto != $old_foto) {
                $oldFilePath = __DIR__ . '/../img/' . $old_foto;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE assorti SET category=?, name=?, foto=?, kolvo=?, cena=?, skidka=? WHERE id=?");
                $stmt->execute(array($category, $name, $foto, $kolvo, $cena, $skidka, $id));
                $message = "Товар обновлен!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO assorti (category, name, foto, kolvo, cena, skidka) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(array($category, $name, $foto, $kolvo, $cena, $skidka));
                $message = "Товар добавлен!";
            }
        } catch (PDOException $e) {
            $message = "Ошибка сохранения: " . $e->getMessage();
        }
    }

    // Удаление товара (только для админа)
    if ($isAdmin && isset($_POST['delete_product'])) {
        $id = (int)$_POST['delete_id'];
        try {
            $stmt = $pdo->prepare("SELECT foto FROM assorti WHERE id = ?");
            $stmt->execute(array($id));
            $product = $stmt->fetch();

            if ($product && !empty($product['foto'])) {
                $filePath = __DIR__ . '/../img/' . $product['foto'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $stmt = $pdo->prepare("DELETE FROM assorti WHERE id = ?");
            $stmt->execute(array($id));
            $message = "Товар удален!";
        } catch (PDOException $e) {
            $message = "Ошибка удаления: " . $e->getMessage();
        }
    }

    // Обновление статуса заказа
    if (isset($_POST['update_order_status'])) {
        $orderId = (int)$_POST['order_id'];
        $newStatus = $_POST['order_status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE zakaz SET status = ? WHERE id = ?");
            $stmt->execute(array($newStatus, $orderId));
            $message = "Статус заказа обновлен!";
        } catch (PDOException $e) {
            $message = "Ошибка обновления статуса: " . $e->getMessage();
        }
    }
    
    // Удаление заказа
    if (isset($_POST['delete_order'])) {
        $orderId = (int)$_POST['delete_order_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM zakaz_items WHERE id_zakaz = ?");
            $stmt->execute(array($orderId));
            $stmt = $pdo->prepare("DELETE FROM zakaz WHERE id = ?");
            $stmt->execute(array($orderId));
            $message = "Заказ удален!";
        } catch (PDOException $e) {
            $message = "Ошибка удаления заказа: " . $e->getMessage();
        }
    }

    // Удаление пользователя (только для админа)
    if ($isAdmin && isset($_POST['delete_user'])) {
        $userId = (int)$_POST['delete_user_id'];
        $userType = $_POST['delete_user_type'];

        try {
            if ($userType == 'client') {
                $stmt = $pdo->prepare("DELETE FROM zakaz_items WHERE id_zakaz IN (SELECT id FROM zakaz WHERE id_klient = ?)");
                $stmt->execute(array($userId));
                $stmt = $pdo->prepare("DELETE FROM zakaz WHERE id_klient = ?");
                $stmt->execute(array($userId));
                $stmt = $pdo->prepare("DELETE FROM klient WHERE id = ?");
                $stmt->execute(array($userId));
            }
            $message = "Пользователь удален!";
        } catch (PDOException $e) {
            $message = "Ошибка удаления";
        }
    }
}

// Получение товаров (только для админа)
if ($isAdmin) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM assorti WHERE skidka = 1 LIMIT 1");
        $stmt->execute();
        $discountProduct = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT * FROM assorti WHERE category = 'Цветы' AND skidka = 0 ORDER BY name");
        $stmt->execute();
        $flowers = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM assorti WHERE category = 'Кашпо' ORDER BY name");
        $stmt->execute();
        $pots = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM assorti WHERE category = 'Декор' ORDER BY name");
        $stmt->execute();
        $decor = $stmt->fetchAll();

        if (isset($_GET['edit_id'])) {
            $editId = (int)$_GET['edit_id'];
            $stmt = $pdo->prepare("SELECT * FROM assorti WHERE id = ?");
            $stmt->execute(array($editId));
            $editProduct = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $message = "Ошибка загрузки данных: " . $e->getMessage();
    }
}

// Получение заказов
try {
    $stmt = $pdo->prepare("
        SELECT z.*, k.name as client_name, k.phone as client_phone, k.`e-mail` as client_email
        FROM zakaz z
        JOIN klient k ON z.id_klient = k.id
        ORDER BY z.date DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}
?>

<!-- Шапка админ-панели -->
<div class="admin-header">
    <div class="admin-menu">
        <?php if ($isAdmin): ?>
        <a href="?page=admin&tab=products" class="admin-menu-link <?php echo $activeTab == 'products' ? 'active' : ''; ?>">Ассортимент</a>
        <?php endif; ?>
        <a href="?page=admin&tab=orders" class="admin-menu-link <?php echo $activeTab == 'orders' ? 'active' : ''; ?>">Заказы</a>
        <?php if ($isAdmin): ?>
        <a href="?page=admin&tab=users" class="admin-menu-link <?php echo $activeTab == 'users' ? 'active' : ''; ?>">Пользователи</a>
        <?php endif; ?>
    </div>
    <div class="admin-buttons">
        <button class="admin-home-btn" onclick="window.location.href='index.php'">На сайт</button>
    </div>
</div>

<!-- Кнопка добавления товара (только для админа) -->
<?php if ($isAdmin && $activeTab == 'products'): ?>
<div class="admin-add-button-container">
    <button class="btn2" onclick="openAddModal()">+ Добавить товар</button>
</div>
<?php endif; ?>

<?php if ($message != ''): ?>
<div class="admin-message"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($isAdmin && $activeTab == 'products'): ?>

<!-- Модальное окно для товаров -->
<div id="productModal" class="admin-modal" style="display: none;">
    <div class="admin-modal-overlay" onclick="closeModal()"></div>
    <div class="admin-modal-container">
        <div class="admin-modal-card">
            <div class="admin-modal-header">
                <h3 id="modalTitle">Добавить товар</h3>
                <button class="admin-modal-close" onclick="closeModal()">×</button>
            </div>
            <form method="POST" id="productForm" action="">
                <input type="hidden" name="product_id" id="productId" value="">
                <input type="hidden" name="cena" id="productPrice" value="">
                <input type="hidden" name="kolvo" id="productKolvo" value="">
                <input type="hidden" name="foto" id="productFoto" value="">
                <input type="hidden" name="old_foto" id="oldFoto" value="">
                <input type="hidden" name="save_product" value="1">

                <div class="admin-modal-image">
                    <div class="image-upload-box" onclick="document.getElementById('fileInput').click()">
                        <div class="image-preview-area" id="imagePreviewArea">
                            <img id="previewImg" src="" alt="Предпросмотр" style="display: none;">
                            <div class="image-placeholder-box" id="placeholderBox">
                                <span>Добавьте картинку</span>
                                <small>Нажмите для выбора файла</small>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="fileInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;" onchange="uploadImage(this.files[0])" />
                    <div id="uploadProgress" style="display: none; text-align: center; margin-top: 10px;">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <small>Загрузка...</small>
                    </div>
                </div>

                <div class="input-group">
                    <label for="productName">Название товара</label>
                    <input type="text" name="name" id="productName" placeholder="Например: Роза красная" required>
                </div>

                <div class="input-row">
                    <div class="input-group half">
                        <label for="priceInput">Цена (руб)</label>
                        <input type="number" name="price_input" id="priceInput" placeholder="0" oninput="updatePriceDisplay(this.value)">
                    </div>
                    <div class="input-group half">
                        <label for="kolvoInput">Количество (шт)</label>
                        <input type="number" name="kolvo_input" id="kolvoInput" placeholder="0" oninput="updateKolvoDisplay(this.value)">
                    </div>
                </div>

                <div class="format-info">
                    <div class="format-badge" id="priceDisplay" style="display: none;"></div>
                    <div class="format-badge" id="kolvoDisplay" style="display: none;"></div>
                </div>

                <div class="input-group">
                    <label>Категория</label>
                    <div class="category-buttons">
                        <label class="category-btn">
                            <input type="radio" name="category" value="Цветы" checked>
                            <span>Цветы</span>
                        </label>
                        <label class="category-btn">
                            <input type="radio" name="category" value="Кашпо">
                            <span>Кашпо</span>
                        </label>
                        <label class="category-btn">
                            <input type="radio" name="category" value="Декор">
                            <span>Декор</span>
                        </label>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="skidka" value="1" id="skidkaCheckbox" />
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Акция / Скидка</span>
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-save">Сохранить</button>
                    <button type="button" class="btn-cancel" id="deleteProductBtn" style="display: none;" onclick="deleteCurrentProduct()">Удалить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Форма удаления товара -->
<form method="POST" id="deleteProductForm" style="display: none;">
    <input type="hidden" name="delete_product" value="1" />
    <input type="hidden" name="delete_id" id="deleteProductId" value="" />
</form>

<script>
function updatePriceDisplay(value) {
    var priceInput = document.getElementById('productPrice');
    var priceDisplay = document.getElementById('priceDisplay');
    if (value && value > 0) {
        priceInput.value = value;
        priceDisplay.innerHTML = Number(value).toLocaleString('ru-RU') + ' руб · шт';
        priceDisplay.style.display = 'inline-block';
    } else {
        priceInput.value = '';
        priceDisplay.style.display = 'none';
    }
}

function updateKolvoDisplay(value) {
    var kolvoInput = document.getElementById('productKolvo');
    var kolvoDisplay = document.getElementById('kolvoDisplay');
    if (value && value > 0) {
        kolvoInput.value = value;
        kolvoDisplay.innerHTML = 'ассортимент · ' + value + ' шт';
        kolvoDisplay.style.display = 'inline-block';
    } else {
        kolvoInput.value = '';
        kolvoDisplay.style.display = 'none';
    }
}

function uploadImage(file) {
    if (!file) return;
    var formData = new FormData();
    formData.append('product_image', file);
    document.getElementById('uploadProgress').style.display = 'block';

    fetch('components/upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('uploadProgress').style.display = 'none';
        if (data.success) {
            document.getElementById('productFoto').value = data.filename;
            document.getElementById('oldFoto').value = '';
            var previewImg = document.getElementById('previewImg');
            var placeholderBox = document.getElementById('placeholderBox');
            previewImg.src = 'img/' + data.filename + '?t=' + Date.now();
            previewImg.style.display = 'block';
            placeholderBox.style.display = 'none';
        } else {
            alert('Ошибка загрузки: ' + data.error);
        }
    })
    .catch(() => {
        document.getElementById('uploadProgress').style.display = 'none';
        alert('Ошибка при загрузке файла');
    });
}

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Добавить товар';
    document.getElementById('productId').value = '';
    document.getElementById('productName').value = '';
    document.getElementById('productPrice').value = '';
    document.getElementById('productKolvo').value = '';
    document.getElementById('priceInput').value = '';
    document.getElementById('kolvoInput').value = '';
    document.getElementById('priceDisplay').style.display = 'none';
    document.getElementById('kolvoDisplay').style.display = 'none';
    document.getElementById('productFoto').value = '';
    document.getElementById('oldFoto').value = '';
    document.getElementById('previewImg').src = '';
    document.getElementById('previewImg').style.display = 'none';
    document.getElementById('placeholderBox').style.display = 'flex';
    document.getElementById('skidkaCheckbox').checked = false;
    document.getElementById('deleteProductBtn').style.display = 'none';
    document.querySelector('input[name="category"][value="Цветы"]').checked = true;
    document.getElementById('productModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function openEditModal(id) {
    window.location.href = '?page=admin&tab=products&edit_id=' + id;
}

function closeModal() {
    document.getElementById('productModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    window.location.href = '?page=admin&tab=products';
}

function deleteCurrentProduct() {
    var productId = document.getElementById('productId').value;
    if (productId && confirm('Вы уверены, что хотите удалить этот товар?')) {
        document.getElementById('deleteProductId').value = productId;
        document.getElementById('deleteProductForm').submit();
    }
}

<?php if ($editProduct): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalTitle').innerText = 'Редактировать товар';
    document.getElementById('productId').value = '<?php echo $editProduct['id']; ?>';
    document.getElementById('productName').value = '<?php echo htmlspecialchars($editProduct['name']); ?>';
    document.getElementById('productPrice').value = '<?php echo $editProduct['cena']; ?>';
    document.getElementById('productKolvo').value = '<?php echo $editProduct['kolvo']; ?>';
    document.getElementById('priceInput').value = '<?php echo $editProduct['cena']; ?>';
    document.getElementById('kolvoInput').value = '<?php echo $editProduct['kolvo']; ?>';
    document.getElementById('priceDisplay').innerHTML = Number(<?php echo $editProduct['cena']; ?>).toLocaleString('ru-RU') + ' руб · шт';
    document.getElementById('priceDisplay').style.display = 'inline-block';
    document.getElementById('kolvoDisplay').innerHTML = 'ассортимент · <?php echo $editProduct['kolvo']; ?> шт';
    document.getElementById('kolvoDisplay').style.display = 'inline-block';
    document.getElementById('productFoto').value = '<?php echo $editProduct['foto']; ?>';
    document.getElementById('oldFoto').value = '<?php echo $editProduct['foto']; ?>';
    document.querySelector('input[name="category"][value="<?php echo $editProduct['category']; ?>"]').checked = true;
    if (<?php echo $editProduct['skidka']; ?>) {
        document.getElementById('skidkaCheckbox').checked = true;
    }
    if ('<?php echo $editProduct['foto']; ?>') {
        document.getElementById('previewImg').src = 'img/<?php echo $editProduct['foto']; ?>';
        document.getElementById('previewImg').style.display = 'block';
        document.getElementById('placeholderBox').style.display = 'none';
    }
    document.getElementById('deleteProductBtn').style.display = 'block';
    document.getElementById('productModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
});
<?php endif; ?>
</script>

<!-- Блок 1 - Скидка недели -->
<div class="block1">
    <div class="block1_2" style="display: flex; justify-content: center; align-items: center; margin-left: 0;">
        <div class="sell" style="margin: 0 auto;">
            <p class="title2">· Скидка недели ·</p>
            <?php if ($discountProduct): ?>
            <div class="card">
                <img class="flow" src="img/<?php echo htmlspecialchars($discountProduct['foto']); ?>" alt="<?php echo htmlspecialchars($discountProduct['name']); ?>">
                <p class="name1"><?php echo htmlspecialchars($discountProduct['name']); ?></p>
                <p class="text3">
                    <?php echo number_format($discountProduct['cena'], 0, ',', ' '); ?> руб · шт<br>
                    ассортимент · <?php echo $discountProduct['kolvo']; ?> шт
                </p>
                <button class="btn2" onclick="openEditModal(<?php echo $discountProduct['id']; ?>)">Редактировать</button>
            </div>
            <?php else: ?>
            <div class="card">
                <p class="text3" style="padding: 40px;">Нет товаров со скидкой</p>
                <button class="btn2" onclick="openAddModal()">+ Добавить</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Блок 2 - Каталог -->
<div class="block2">
    <p class="title3">КАТАЛОГ</p>
    <div class="cards-container">
        <?php if (count($flowers) > 0): ?>
            <?php foreach ($flowers as $flower): ?>
                <div class="card">
                    <img class="flow" src="img/<?php echo htmlspecialchars($flower['foto']); ?>" alt="<?php echo htmlspecialchars($flower['name']); ?>">
                    <p class="name1"><?php echo htmlspecialchars($flower['name']); ?></p>
                    <p class="text3">
                        <?php echo number_format($flower['cena'], 0, ',', ' '); ?> руб · шт<br>
                        ассортимент · <?php echo $flower['kolvo']; ?> шт
                    </p>
                    <button class="btn2" onclick="openEditModal(<?php echo $flower['id']; ?>)">Редактировать</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Товары не найдены</p>
        <?php endif; ?>
    </div>
</div>

<!-- Блок 3 - Кашпо -->
<div class="block3">
    <p class="title3">КАШПО</p>
    <div class="cards-container">
        <?php if (count($pots) > 0): ?>
            <?php foreach ($pots as $pot): ?>
                <div class="card">
                    <div class="image-wrapper">
                        <img class="flow" src="img/<?php echo htmlspecialchars($pot['foto']); ?>" alt="<?php echo htmlspecialchars($pot['name']); ?>">
                    </div>
                    <div class="content-wrapper">
                        <p class="name1"><?php echo htmlspecialchars($pot['name']); ?></p>
                        <p class="text3">
                            <?php echo number_format($pot['cena'], 0, ',', ' '); ?> руб · шт<br>
                            ассортимент · <?php echo $pot['kolvo']; ?> шт
                        </p>
                        <button class="btn2" onclick="openEditModal(<?php echo $pot['id']; ?>)">Редактировать</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Товары не найдены</p>
        <?php endif; ?>
    </div>
</div>

<!-- Блок 4 - Декор -->
<div class="block4">
    <p class="title3">ДЕКОР ДЛЯ ОФОРМЛЕНИЯ БУКЕТА</p>
    <div class="cards-container">
        <?php if (count($decor) > 0): ?>
            <?php foreach ($decor as $item): ?>
                <div class="card">
                    <div class="image-wrapper">
                        <img class="flow" src="img/<?php echo htmlspecialchars($item['foto']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="content-wrapper">
                        <p class="name1"><?php echo htmlspecialchars($item['name']); ?></p>
                        <p class="text3">
                            <?php echo number_format($item['cena'], 0, ',', ' '); ?> руб · шт<br>
                            ассортимент · <?php echo $item['kolvo']; ?> шт
                        </p>
                        <button class="btn2" onclick="openEditModal(<?php echo $item['id']; ?>)">Редактировать</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Товары не найдены</p>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<!-- Раздел ЗАКАЗЫ -->
<?php if ($activeTab == 'orders'): ?>
<div class="admin-users-section">
    <h2 class="admin-users-title">Управление заказами</h2>
    <div class="table-wrapper">
        <table class="admin-users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Клиент</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Способ оплаты</th>
                    <th>Способ доставки</th>
                    <th>Адрес</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['client_phone']); ?></td>
                            <td><?php echo htmlspecialchars($order['client_email']); ?></td>
                            <td><?php echo htmlspecialchars($order['spos_opl']); ?></td>
                            <td><?php echo htmlspecialchars($order['spos_dos']); ?></td>
                            <td><?php echo htmlspecialchars($order['add_dos']); ?></td>
                            <td><?php echo number_format($order['summa'], 0, ',', ' '); ?> руб</td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($order['date'])); ?></td>
                            <td>
                                <div class="admin-card-buttons">
                                    <button class="btn2" onclick="editOrder(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">Статус</button>
                                    <button class="btn-delete" onclick="deleteOrder(<?php echo $order['id']; ?>)">Удалить</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" style="text-align: center;">Нет заказов</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editOrder(orderId, currentStatus) {
    var newStatus = prompt('Введите новый статус заказа:\n\nДоступные статусы:\n- новый\n- в обработке\n- доставлен\n- отменен', currentStatus);
    if (newStatus && newStatus !== currentStatus) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'update_order_status';
        input1.value = '1';
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'order_id';
        input2.value = orderId;
        var input3 = document.createElement('input');
        input3.type = 'hidden';
        input3.name = 'order_status';
        input3.value = newStatus;
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteOrder(orderId) {
    if (confirm('Вы уверены, что хотите удалить этот заказ?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'delete_order';
        input1.value = '1';
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'delete_order_id';
        input2.value = orderId;
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php endif; ?>

<!-- Таблица пользователей (только для админа) -->
<?php if ($isAdmin && $activeTab == 'users'): ?>
<div class="admin-users-section">
    <h2 class="admin-users-title">Пользователи</h2>
    <?php
    try {
        $clients = $pdo->query("SELECT id, name, `e-mail` as email, phone FROM klient ORDER BY id")->fetchAll();
    } catch (PDOException $e) {
        $clients = [];
    }
    ?>
    <div class="table-wrapper">
        <table class="admin-users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clients) > 0): ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo $client['id']; ?></td>
                            <td><?php echo htmlspecialchars($client['name']); ?></td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['phone']); ?></td>
                            <td><button class="admin-delete-user" onclick="deleteUser(<?php echo $client['id']; ?>, 'client')">Удалить</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center;">Нет зарегистрированных пользователей</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteUser(id, type) {
    if (confirm('Удалить этого пользователя?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'delete_user';
        input1.value = '1';
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'delete_user_id';
        input2.value = id;
        var input3 = document.createElement('input');
        input3.type = 'hidden';
        input3.name = 'delete_user_type';
        input3.value = type;
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php endif; ?>