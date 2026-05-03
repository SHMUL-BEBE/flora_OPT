<?php
// Подключаем базу данных
require_once __DIR__ . '/../config/db.php';
?>

<div class="block1">
    <img class="flowers1" src="img/flowers1.png" alt="пионы">
    <img class="flowers2" src="img/flowers2.png" alt="лилии">
    
    <div class="block1_1">
        <img class="lavka" src="img/lavka.png" alt="лавка">
        <div class="txt">
            <p class="title1">Сделайте мир ярче<br>с «Флора Опт»!</p>
            <p class="text1">
                Хотите, чтобы ваш букет<br>
                или оформление зала<br>
                запомнились надолго?<br><br>
                Начните с правильных цветов.<br><br>
                ──────────<br>
                «Флора Опт» — огромный выбор<br>
                свежесрезанных растений<br>
                по доступным оптовым ценам.
            </p>
        </div>
    </div>

    <div class="block1_2">
        <div class="sell">
            <p class="title2">· Скидка недели ·</p>
            <?php
            // Получаем товар со скидкой из БД
            $stmt = $pdo->prepare("SELECT * FROM assorti WHERE skidka = 1 LIMIT 1");
            $stmt->execute();
            $discountProduct = $stmt->fetch();
            
            if ($discountProduct):
            ?>
            <div class="card">
                <img class="flow" src="img/<?php echo htmlspecialchars($discountProduct['foto']); ?>" alt="<?php echo htmlspecialchars($discountProduct['name']); ?>">
                <p class="name1"><?php echo htmlspecialchars($discountProduct['name']); ?></p>
                <p class="text3">
                    <?php echo number_format($discountProduct['cena'], 0, ',', ' '); ?> ₽ · шт<br>
                    ассортимент · <?php echo $discountProduct['kolvo']; ?> шт
                </p>
                <input class="btn2" type="button" value="В корзину" onclick="addToCart(<?php echo $discountProduct['id']; ?>, '<?php echo htmlspecialchars($discountProduct['name']); ?>', <?php echo $discountProduct['cena']; ?>)">
            </div>
            <?php else: ?>
            <div class="card">
                <p class="text3" style="padding: 40px;">Нет товаров со скидкой</p>
            </div>
            <?php endif; ?>
        </div>
        <div class="txt">
            <p class="text2">
                Для свадьбы, корпоратива<br>
                или вашего магазина:<br><br>
                розы нежности<br>
                стойкие хризантемы<br>
                ароматные пионы<br><br>
                ──────────<br>
                От 5 штук.<br>
                ──────────<br>
                Просматривайте каталог.<br>
                Сравнивайте цены.<br>
                Заказывайте свежесть<br>
                сегодня.
            </p>
            <input class="btn1" type="button" value="Заказать" onclick="window.location.href='index.php#dostavka'">
        </div>
    </div>
</div>