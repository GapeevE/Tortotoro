<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tortotoro - Вход</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md border-2 border-orange-100">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600 mb-3">
                    Tortotoro
                </h1>
                <p class="text-gray-500 font-medium">Система управления кондитерской</p>
            </div>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-lg border border-red-200">
                    <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                </div>
            <?php endif; ?>
            <form id="loginForm" class="space-y-5" method="POST" action="input.php">
                <div>
                    <label class="block text-gray-600 mb-2 font-medium">Логин</label>
                    <input type="text" name="login" required
                            pattern="^[a-zA-Z0-9_]{4,20}$"
                            title="4-20 символов, только буквы, цифры и подчеркивание"
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-200 transition-all">
                </div>
                <div>
                    <label class="block text-gray-600 mb-2 font-medium">Пароль</label>
                    <input type="password" name="password" required
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                            title="Минимум 8 символов: 1 заглавная, 1 строчная буква, 1 цифра и 1 спецсимвол (@$!%*?&)"
                            class="w-full px-4 py-3 border-2 border-orange-100 rounded-lg focus:outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-200 transition-all">
                </div>
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white py-3 rounded-lg font-semibold
                               hover:from-orange-600 hover:to-amber-600 shadow-md hover:shadow-lg transition-all">
                    Войти
                </button>
            </form>
        </div>
    </div>
</body>
</html>