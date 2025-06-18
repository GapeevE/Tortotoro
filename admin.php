<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещен');
} 
require 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT * FROM user ORDER BY role_id, name");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка получения данных: " . $e->getMessage());
}

try {
    $stmt = $pdo->query("SELECT * FROM shift ORDER BY time_start");
    $shifts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка получения данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tortotoro - Администрирование</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50">
    <nav class="bg-gradient-to-r from-orange-600 to-amber-600 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-white">Управление кондитерской Tortotoro</h1>
            <button onclick="logout()" class="text-orange-100 hover:text-white flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>                  
                Выйти из системы
            </button>
        </div>
    </nav>
    <div class="container mx-auto p-6 grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-lg border-2 border-orange-100 p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-orange-800 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Команда кондитерской
                </h2>
                <div class="flex flex-col md:flex-row gap-3 mb-6">
                    <div class="flex-1 relative">
                        <input type="text" placeholder="Найти сотрудника..." 
                               class="w-full pl-10 pr-4 py-3 border-2 border-orange-100 rounded-xl focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-orange-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>                          
                    </div>
                    <button onclick="openAddUserModal()" 
                            class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-6 py-3 rounded-xl hover:from-orange-600 hover:to-amber-600 flex items-center gap-2 transition-all">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>                          
                        Новый сотрудник
                    </button>
                </div>
                <script>
                    const allUsers = <?= json_encode(array_map(function($user) {
                        return [
                            'id' => $user['id_user'],
                            'name' => htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'),
                            'login' => htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8'),
                            'role' => $user['role_id'],
                            'photo' => $user['photo_file'] ? htmlspecialchars($user['photo_file'], ENT_QUOTES, 'UTF-8') : null
                        ];
                    }, $users)) ?>;

                    function filterUsers(searchText) {
                        return allUsers.filter(user => 
                            user.name.toLowerCase().includes(searchText.toLowerCase())
                        );
                    }

                    function renderUsers(users) {
                        const container = document.querySelector('.users-list');
                        container.innerHTML = '';
                        users.forEach(user => {
                            const userDiv = document.createElement('div');
                            userDiv.className = 'flex items-center p-4 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors';
                            
                            const avatar = user.photo ? 
                                `<div class="w-12 h-12 rounded-full bg-cover bg-center mr-4 shadow-sm" style="background-image: url('${user.photo}')"></div>` :
                                `<div class="w-12 h-12 rounded-full bg-orange-200 flex items-center justify-center mr-4 shadow-sm">
                                    <svg class="w-6 h-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </div>`;

                            const role = {
                                1: 'Администратор',
                                2: 'Официант',
                                3: 'Шеф-кондитер'
                            }[user.role] || 'Неизвестно';

                            userDiv.innerHTML = `
                                ${avatar}
                                <div class="flex-1">
                                    <h3 class="font-semibold text-orange-900">${user.name}</h3>
                                    <p class="text-sm text-orange-600">${role}</p>
                                </div>
                                <button onclick="deleteUser(${user.id})" class="text-orange-500 hover:text-orange-700 p-2 cursor-pointer">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>                              
                                </button>
                            `;
                            container.appendChild(userDiv);
                        });
                    }
                    document.querySelector('input[placeholder="Найти сотрудника..."]').addEventListener('input', function(e) {
                        const filteredUsers = filterUsers(e.target.value);
                        renderUsers(filteredUsers);
                    });
                    document.addEventListener('DOMContentLoaded', () => renderUsers(allUsers));
                    function deleteUser(userId) {
                        if (confirm('Вы уверены, что хотите удалить этого сотрудника?')) {
                            fetch(`delete_user.php?id=${userId}`, { 
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json' 
                                }
                            })
                            .then(response => {
                                return response.json()
                                    .then(data => {
                                        if (!response.ok) {
                                            throw new Error(data.error || `Ошибка ${response.status}`);
                                        }
                                        return data;
                                    });
                            })
                            .then(data => {
                                const index = allUsers.findIndex(u => u.id === userId);
                                if (index > -1) {
                                    allUsers.splice(index, 1);
                                    renderUsers(allUsers);
                                }
                            })
                            .catch(error => {
                                alert(`Ошибка при удалении: ${error.message}`);
                                console.error('Полная ошибка:', error);
                            });
                        }
                    }
                </script>
                <div class="h-[calc(100vh-280px)] overflow-y-auto space-y-3 users-list"></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg border-2 border-orange-100 p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-orange-800 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    График смен
                </h2>
                <button onclick="openShiftModal()" class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-6 py-3 rounded-xl hover:from-orange-600 hover:to-amber-600 w-full mb-6 flex items-center gap-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>                      
                    Запланировать новую смену
                </button>
                <div class="space-y-4">
                    <?php foreach ($shifts as $shift): ?>
                    <div onclick="openShiftModal(<?= $shift['id_shift'] ?>)" 
                         class="p-4 bg-orange-50 border-2 border-orange-100 rounded-xl hover:border-orange-200 cursor-pointer transition-all">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-semibold text-orange-900">Смена #<?= $shift['id_shift'] ?></h3>
                                <p class="text-sm text-orange-700">
                                    <?= date('d M H:i', strtotime($shift['time_start'])) ?> — 
                                    <?= date('H:i', strtotime($shift['time_end'])) ?>
                                </p>
                            </div>
                            <?php if ($shift['status'] === 'active'): ?>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                Активна
                            </span>
                            <?php elseif ($shift['status'] === 'pending'): ?>
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                                В ожидании
                            </span>
                            <?php else: ?>
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm font-medium">
                                Завершена
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-orange-600">
                            <?= count($shift['employees'] ?? []) ?> сотрудников
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div id="addUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 bg-orange-100 rounded-t-2xl">
                <h3 class="text-xl font-bold text-orange-900">Новый сотрудник</h3>
                <button onclick="closeModal()" class="text-orange-700 hover:text-orange-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>                      
                </button>
            </div>    
            <form id="userForm" class="p-6 space-y-5" enctype="multipart/form-data">
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">ФИО *</label>
                    <input type="text" name="name" required 
                           class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200"
                           pattern="^[А-Яа-яЁёA-Za-z\s-]{2,50}$"
                           title="Допустимы только буквы, пробелы и дефисы (2-50 символов)">                           
                </div>
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Логин *</label>
                    <input type="text" name="login" required 
                           class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200"
                           pattern="^[a-zA-Z0-9_]{4,20}$"
                           title="4-20 символов, только буквы, цифры и подчеркивание">
                </div>
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Пароль *</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Фотография</label>
                    <div class="flex items-center gap-2">
                        <input type="file" name="photo" accept="image/*" 
                               class="block w-full text-sm text-orange-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200 transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Должность *</label>
                    <select name="role_id" required 
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                        <option value="1">Администратор</option>
                        <option value="2">Официант</option>
                        <option value="3">Шеф-кондитер</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeModal()" 
                            class="px-6 py-2 text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                        Отмена
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div id="addShiftModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 bg-orange-100 rounded-t-2xl">
                <h3 class="text-xl font-bold text-orange-900">Планирование смены</h3>
                <button onclick="closeShiftModal()" class="text-orange-700 hover:text-orange-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>                      
                </button>
            </div>
            <form id="shiftForm" class="p-6 space-y-6">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-orange-800 mb-2">Начало смены *</label>
                        <input type="datetime-local" required 
                                name="start"
                                class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-orange-800 mb-2">Конец смены *</label>
                        <input type="datetime-local" required 
                                name="end"
                                class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Сотрудники *</label>
                    <select multiple 
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200 h-32"
                            id="shiftEmployees"
                            name="employees[]">
                    </select>
                    <div class="mt-2 space-y-2" id="selectedEmployees"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-orange-800 mb-2">Статус *</label>
                    <select required 
                            name="status"
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:border-orange-300 focus:ring-2 focus:ring-orange-200">
                        <option value="pending">В ожидании</option>
                        <option value="active">Активна</option>
                        <option value="closed">Завершена</option>
                    </select>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-orange-800 mb-2">Заказы смены</h4>
                    <div class="space-y-3 parent-orders"></div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeShiftModal()" 
                            class="px-6 py-2 text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                        Отмена
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function logout() {
            fetch('logout.php')
                .then(() => window.location.href = 'index.php')
                .catch(error => console.error('Ошибка выхода:', error));
        }

        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }

        function closeShiftModal() {
            document.getElementById('addShiftModal').classList.add('hidden');
        }

        document.getElementById('addUserModal').addEventListener('click', function(e) {
            if(e.target === this) closeModal();
        });

        function updateSelectedEmployees() {
            const select = document.getElementById('shiftEmployees');
            const container = document.getElementById('selectedEmployees');
            container.innerHTML = '';
            Array.from(select.selectedOptions).forEach(option => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between bg-orange-50 p-2 rounded-lg';
                div.innerHTML = `
                    <span class="text-orange-700">${option.textContent}</span>
                    <button onclick="this.parentElement.remove()" 
                            class="text-orange-500 hover:text-orange-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;
                container.appendChild(div);
            });
        }
        let currentShiftId = null;
        async function openShiftModal(shiftId = null) {
            currentShiftId = shiftId;
            const modal = document.getElementById('addShiftModal');
            modal.classList.remove('hidden');
            try {
                const response = await fetch('get_users.php');
                const users = await response.json();
                const select = document.getElementById('shiftEmployees');
                select.innerHTML = users.map(user => `
                    <option value="${user.id}">
                        ${user.name} (${user.role})
                    </option>
                `).join('');
                if (shiftId) {
                    const shiftResponse = await fetch(`get_shift.php?id=${shiftId}`);
                    const shiftData = await shiftResponse.json();
                    document.querySelector('[name="start"]').value = shiftData.start;
                    document.querySelector('[name="end"]').value = shiftData.end;
                    document.querySelector('[name="status"]').value = shiftData.status;
                    const parentOrders = document.querySelector('.parent-orders');
                    parentOrders.innerHTML = '';
                    if (shiftData.orders) {                        
                        shiftData.orders.forEach(order => {
                            const orderElement = document.createElement('div');
                            orderElement.className = 'p-3 border-2 border-orange-100 rounded-lg';
                            orderElement.innerHTML = `
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="font-medium text-orange-900">Заказ #${order.id_order}</span>
                                        <span class="text-sm text-orange-600 ml-2">${new Date(order.created_at).toLocaleString()}</span>
                                    </div>
                                    <span class="${getStatusClasses(order.status)} px-2 py-1 rounded-full text-sm">
                                        ${getStatusText(order.status)}
                                    </span>
                                </div>
                                <div class="text-sm mb-2 text-orange-700">
                                    <p>Позиции: ${order.items}</p>
                                    <p>Сумма: ${order.total}</p>
                                </div>
                                <div class="text-sm text-orange-600">
                                    Официант: ${order.name}
                                </div>
                            `;
                            parentOrders.appendChild(orderElement);
                        });
                    }
                    const employeeIds = shiftData.employees.map(id => Number(id));
                    employeeIds.forEach(employeeId => {
                        const option = Array.from(select.options).find(
                            opt => Number(opt.value) === employeeId
                        );
                        if (option) option.selected = true;
                    });
                }
                updateSelectedEmployees();
            } catch (error) {
                console.error('Ошибка загрузки данных:', error);
            }
        }
        document.getElementById('shiftEmployees').addEventListener('change', updateSelectedEmployees);
        document.getElementById('shiftForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formDataShift = {
                start: document.querySelector('#shiftForm [name="start"]').value,
                end: document.querySelector('#shiftForm [name="end"]').value,
                employees: Array.from(document.getElementById('shiftEmployees').selectedOptions)
                            .map(option => option.value),
                status: document.querySelector('#shiftForm [name="status"]').value
            };
            try {
                const url = currentShiftId ? 
                    `save_shift.php?id=${currentShiftId}` : 
                    'save_shift.php';
                const method = currentShiftId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formDataShift)
                });
                if (!response.ok) throw new Error('Ошибка сервера');
                alert(currentShiftId ? 'Смена обновлена!' : 'Смена создана!');
                closeShiftModal();
                window.location.reload();
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        });
        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('save_user.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(await response.text());
                alert('Сотрудник успешно добавлен!');
                closeModal();
                window.location.reload();
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        });
        function getStatusText(status) {
            return {
                'pending': 'Ожидает',
                'preparing': 'В работе',
                'ready': 'Готов',
                'paid': 'Оплачен',
                'canceled': 'Отменен'
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
    </script>
</body>
</html>