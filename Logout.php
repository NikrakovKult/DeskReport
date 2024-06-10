
<?php
// Закрытие сессии
session_start();
session_unset(); // удаляем все переменные сессии
session_destroy(); // уничтожаем сессию

// Удаляем cookie сессии, если она существует
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Перенаправление на страницу Login.php
header('Location: Login.php');
exit;
?>