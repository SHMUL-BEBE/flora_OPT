<script>
function addToCart(id, name, price) {
    <?php if (!isset($_SESSION['user_id'])): ?>
        if (confirm('Для добавления в корзину необходимо авторизоваться. Перейти на страницу входа?')) {
            window.location.href = 'index.php?page=auth';
        }
        return;
    <?php endif; ?>
    
    fetch('components/trash.php?add=1', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, name: name, price: price })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Товар добавлен в корзину!');
        }
    })
    .catch(error => console.error('Ошибка:', error));
}

document.getElementById('logoBtn').addEventListener('click', function() {
    window.location.href = 'index.php';
});

<?php if (!isset($_SESSION['user_id'])): ?>
document.getElementById('profileBtn').addEventListener('click', function() {
    window.location.href = 'index.php?page=auth';
});
<?php endif; ?>

document.getElementById('trashBtn').addEventListener('click', function() {
    window.location.href = 'index.php?page=trash';
});
</script>