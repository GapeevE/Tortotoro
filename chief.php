<?php
session_start();
if ($_SESSION['user_role'] !== 'chief') {
    http_response_code(403);
    exit('Доступ запрещен');
} 
require 'db_connect.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            o.id_order,
            o.created_at,
            o.status,
            GROUP_CONCAT(CONCAT(d.name, '×', i.count) SEPARATOR '||') as items,
            SUM(d.price * i.count) as total
        FROM `order` o
        JOIN item i ON o.id_order = i.order_id
        JOIN dish d ON i.dish_id = d.id_dish
        WHERE o.shift_id = ?
        GROUP BY o.id_order
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['shift_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка получения заказов: " . $e->getMessage());
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'Новый заказ',
        'preparing' => 'В приготовлении',
        'ready' => 'Готов к подаче',
        'paid' => 'Оплачен',
        'canceled' => 'Отменён'
    ];
    return $statuses[$status] ?? 'Неизвестно';
}

function getStatusClasses($status) {
    $classes = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'preparing' => 'bg-blue-100 text-blue-800',
        'ready' => 'bg-green-100 text-green-800',
        'paid' => 'bg-gray-100 text-gray-800',
        'canceled' => 'bg-red-100 text-red-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tortotoro - Кухня</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50">
    <nav class="bg-gradient-to-r from-orange-600 to-amber-600 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25Z" />
                </svg>
                Рабочее место шеф-кондитера
            </h1>
            <button onclick="logout()" class="text-orange-100 hover:text-white flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>                  
                Завершить смену
            </button>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-xl shadow-lg border-2 border-orange-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($orders as $order): ?>
                <div class="order-card bg-orange-50 p-4 rounded-xl border-2 border-orange-100 hover:border-orange-200 cursor-pointer transition-all" 
                     data-order-id="<?= $order['id_order'] ?>">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-lg text-orange-900">Заказ #<?= $order['id_order'] ?></h3>
                            <p class="text-sm text-orange-600">
                                <?= date('H:i d.m.Y', strtotime($order['created_at'])) ?>
                            </p>
                        </div>
                        <span class="order-status px-3 py-1 rounded-full text-sm font-medium <?= getStatusClasses($order['status']) ?>" 
                              data-status="<?= $order['status'] ?>">
                            <?= getStatusText($order['status']) ?>
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-orange-800 mb-2">Состав заказа:</h4>
                        <ul class="text-sm space-y-2">
                            <?php foreach (explode('||', $order['items']) as $item): ?>
                            <?php list($name, $count) = explode('×', $item); ?>
                            <li class="flex justify-between text-orange-700">
                                <span><?= htmlspecialchars($name) ?></span>
                                <span class="font-medium"><?= $count ?> порц.</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="flex justify-between items-center border-t border-orange-100 pt-3">
                        <span class="font-medium text-orange-800">Время приготовления:</span>
                        <span class="font-bold text-orange-900">~<?= rand(15, 45) ?> мин</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div id="editOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 bg-orange-100 rounded-t-2xl">
                <h3 class="text-xl font-bold text-orange-900">Статус приготовления</h3>
                <button onclick="closeEditOrderModal()" class="text-orange-700 hover:text-orange-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editOrderForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Изменить статус</label>
                    <select id="orderStatus" 
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200" required>
                        <option value="preparing">В приготовлении</option>
                        <option value="ready">Готов к подаче</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditOrderModal()"  
                            class="px-6 py-2 text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                        Отмена
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        Обновить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let currentOrderId = null;

    function openEditOrderModal(orderId, currentStatus) {
        currentOrderId = orderId;
        document.getElementById('orderStatus').value = currentStatus;
        document.getElementById('editOrderModal').classList.remove('hidden');
    }

    function closeEditOrderModal() {
        document.getElementById('editOrderModal').classList.add('hidden');
    }

    document.getElementById('editOrderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const newStatus = document.getElementById('orderStatus').value;
        try {
            const response = await fetch('update_order_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    order_id: currentOrderId,
                    status: newStatus
                })
            });
            
            if (!response.ok) throw new Error('Ошибка обновления статуса');
            
            const statusElement = document.querySelector(`[data-order-id="${currentOrderId}"] .order-status`);
            statusElement.textContent = getStatusText(newStatus);
            statusElement.className = `order-status px-3 py-1 rounded-full text-sm font-medium ${getStatusClasses(newStatus)}`;
            closeEditOrderModal();
            alert('Статус успешно обновлён!');
        } catch (error) {
            alert('Ошибка: ' + error.message);
        }
    });

    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const currentStatus = this.querySelector('.order-status').dataset.status;
            openEditOrderModal(orderId, currentStatus);
        });
    });

    function getStatusText(status) {
        return {
            'pending': 'Новый заказ',
            'preparing': 'В приготовлении',
            'ready': 'Готов к подаче',
            'paid': 'Оплачен',
            'canceled': 'Отменён'
        }[status] || 'Неизвестно';
    }

    function getStatusClasses(status) {
        return {
            'pending': 'bg-yellow-100 text-yellow-800',
            'preparing': 'bg-blue-100 text-blue-800',
            'ready': 'bg-green-100 text-green-800',
            'paid': 'bg-gray-100 text-gray-800',
            'canceled': 'bg-red-100 text-red-800'
        }[status] || 'bg-gray-100 text-gray-800';
    }

    function logout() {
        fetch('logout.php')
            .then(() => window.location.href = 'index.php')
            .catch(error => console.error('Ошибка выхода:', error));
    }
    </script>
</body>
</html>