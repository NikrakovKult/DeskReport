<?php
// Подключаемся к базе данных
$conn = mysqli_connect("localhost", "root", "", "DeskReport");

// Проверяем соединение
if (!$conn) {
  die("Connection failed: ". mysqli_connect_error());
}

// Получаем данные из формы
$title = $_POST['title'];
$text = $_POST['text'];
$user_id = $_POST['user_id'];

// Создаем заметку в базе данных
$query = "INSERT INTO zametki (user_id, Title, Text) VALUES (?,?,?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $text);
mysqli_stmt_execute($stmt);

// Выводим блок заметки в формате JSON
$response = array('id' => mysqli_insert_id($conn), 'title' => $title, 'text' => $text, 'success' => true);
echo json_encode($response);

// Закрываем соединение
mysqli_close($conn);
?>