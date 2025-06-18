<?php
session_start();
if ($_SESSION['user_role'] !== 'waiter') {
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
        WHERE o.user_id = ? AND o.shift_id = ?
        GROUP BY o.id_order
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['shift_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка получения заказов: " . $e->getMessage());
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'Принят',
        'preparing' => 'В работе',
        'ready' => 'Готов',
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
    <title>Tortotoro - Рабочее место</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50">
    <nav class="bg-gradient-to-r from-orange-600 to-amber-600 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-white">Рабочее место официанта</h1>
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
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1 relative">
                    <input type="text" id="searchInput" placeholder="Поиск по заказам..." 
                           class="w-full pl-10 pr-4 py-3 border-2 border-orange-100 rounded-xl focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                    <svg class="w-5 h-5 absolute left-3 top-3.5 text-orange-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>                      
                </div>
                <button onclick="openOrderModal()" 
                        class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-6 py-3 rounded-xl hover:from-orange-600 hover:to-amber-600 flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>                      
                    Оформить заказ
                </button>
            </div>

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
                        <span class="font-medium text-orange-800">Итого к оплате:</span>
                        <span class="font-bold text-orange-900"><?= $order['total'] ?>₽</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно нового заказа -->
    <div id="addOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 bg-orange-100 rounded-t-2xl">
                <h3 class="text-xl font-bold text-orange-900">Новый заказ</h3>
                <button onclick="closeOrderModal()" class="text-orange-700 hover:text-orange-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="orderForm" class="p-6 space-y-4">
                <div class="flex gap-2">
                    <select id="menuItem" class="flex-1 px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                        <option value="">Выберите десерт</option>
                    </select>
                    <input type="number" id="quantity" min="1" value="1" 
                           class="w-20 px-3 py-2 border-2 border-orange-100 rounded-lg text-center">
                    <button type="button" onclick="addItem()" 
                            class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                        Добавить
                    </button>
                </div>
                <div class="border-2 border-orange-100 rounded-lg p-4">
                    <div id="selectedItems" class="space-y-3">
                    </div>
                    <div class="pt-4 mt-4 border-t border-orange-100">
                        <div class="flex justify-between font-bold text-orange-900">
                            <span>Общая сумма:</span>
                            <span id="totalAmount">0₽</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeOrderModal()" 
                            class="px-6 py-2 text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                        Отмена
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        Подтвердить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div id="editOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 bg-orange-100 rounded-t-2xl">
                <h3 class="text-xl font-bold text-orange-900">Управление заказом</h3>
                <button onclick="closeEditOrderModal()" class="text-orange-700 hover:text-orange-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editOrderForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Статус заказа</label>
                    <select id="orderStatus" 
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200" required>
                        <option value="pending">Принят</option>
                        <option value="paid">Оплачен</option>
                        <option value="canceled">Отменён</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditOrderModal()"  
                            class="px-6 py-2 text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                        Отмена
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        Обновить статус
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let currentOrderId = null;
    let orderItems = [];
    let total = 0;

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
                body: JSON.stringify({order_id: currentOrderId, status: newStatus})
            });
            
            if (!response.ok) throw new Error('Ошибка обновления');
            
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

    async function loadDishes() {
        try {
            const response = await fetch('get_dishes.php');
            const dishes = await response.json();
            const select = document.getElementById('menuItem');
            
            select.innerHTML = '<option value="">Выберите десерт</option>' + 
                dishes.map(dish => `
                    <option value="${dish.id_dish}" data-price="${dish.price}">
                        ${dish.name} (${dish.price}₽)
                    </option>
                `).join('');
        } catch (error) {
            console.error('Ошибка загрузки меню:', error);
        }
    }

    function openOrderModal() {
        document.getElementById('addOrderModal').classList.remove('hidden');
        orderItems = [];
        total = 0;
        updateOrderDisplay();
    }

    function closeOrderModal() {
        document.getElementById('addOrderModal').classList.add('hidden');
    }

    function addItem() {
        const select = document.getElementById('menuItem');
        const dishId = select.value;
        const dishName = select.selectedOptions[0].text.split(' (')[0];
        const price = parseFloat(select.selectedOptions[0].dataset.price);
        const quantity = parseInt(document.getElementById('quantity').value);
        
        if (!dishId || quantity < 1) return;
        
        orderItems.push({dishId, dishName, price, quantity});
        updateOrderDisplay();
    }

    function updateOrderDisplay() {
        const container = document.getElementById('selectedItems');
        total = 0;
        container.innerHTML = '';
        
        orderItems.forEach((item, index) => {
            total += item.price * item.quantity;
            container.innerHTML += `
                <div class="flex justify-between items-center bg-orange-50 p-3 rounded-lg">
                    <div>
                        <span class="font-medium text-orange-900">${item.dishName}</span>
                        <span class="text-sm text-orange-600">${item.price}₽ × ${item.quantity}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-medium text-orange-900">${item.price * item.quantity}₽</span>
                        <button onclick="removeItem(${index})" class="text-orange-500 hover:text-orange-700">✕</button>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('totalAmount').textContent = `${total}₽`;
    }

    function removeItem(index) {
        orderItems.splice(index, 1);
        updateOrderDisplay();
    }

    document.getElementById('orderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (orderItems.length === 0) {
            alert('Добавьте хотя бы одну позицию');
            return;
        }
        
        try {
            const response = await fetch('save_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    shift_id: <?= $_SESSION['shift_id'] ?? 'null' ?>,
                    user_id: <?= $_SESSION['user_id'] ?? 'null' ?>,
                    items: orderItems
                })
            });
            
            if (!response.ok) throw new Error('Ошибка сохранения');
            
            alert('Заказ успешно оформлен!');
            closeOrderModal();
            window.location.reload();
        } catch (error) {
            alert('Ошибка: ' + error.message);
        }
    });

    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.trim().toLowerCase();
        document.querySelectorAll('.order-card').forEach(card => {
            const items = Array.from(card.querySelectorAll('li span:first-child'));
            const itemNames = items.map(item => item.textContent.toLowerCase()).join(' ');
            card.style.display = itemNames.includes(searchTerm) ? 'block' : 'none';
        });
    });

    function logout() {
        fetch('logout.php')
            .then(() => window.location.href = 'index.php')
            .catch(error => console.error('Ошибка выхода:', error));
    }

    document.addEventListener('DOMContentLoaded', loadDishes);
    </script>
</body>
</html>