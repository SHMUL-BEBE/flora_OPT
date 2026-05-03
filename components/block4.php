<?php
include __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare("SELECT * FROM assorti WHERE category = 'Декор' AND skidka = 0");
$stmt->execute();
$flowers = $stmt->fetchAll();
?>
<div class="block4">
    <p class="title3">ДЕКОР ДЛЯ ОФОРМЛЕНИЯ БУКЕТА</p>
    <div class="cards-container">
        <?php if (count($flowers) > 0): ?>
            <?php foreach ($flowers as $flower): ?>
                <div class="card">
                    <img class="flow" src="img/<?= htmlspecialchars($flower['foto']) ?>" alt="<?= htmlspecialchars($flower['name']) ?>">
                    <p class="name1"><?= htmlspecialchars($flower['name']) ?></p>
                    <p class="text3">
                        <?= number_format($flower['cena'], 0, ',', ' ') ?>₽ · шт<br>
                        ассортимент · <?= htmlspecialchars($flower['kolvo']) ?> шт
                    </p>
                    <input class="btn2" type="button" value="В корзину" onclick="addToCart(<?= $flower['id'] ?>, '<?= htmlspecialchars($flower['name']) ?>', <?= $flower['cena'] ?>)">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Товары не найдены</p>
        <?php endif; ?>
    </div>
</div>