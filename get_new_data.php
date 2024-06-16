<?php
// Подключаемся к базе данных
$conn = mysqli_connect("localhost", "root", "", "DeskReport");

// Определяем переменные для начала и конца недели
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

$daysOfWeek = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
$newData = array_fill(0, 7, 0);
$inWorkData = array_fill(0, 7, 0);
$pausedData = array_fill(0, 7, 0);
$completedData = array_fill(0, 7, 0);


$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'Новая' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['Date_by'];
    $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 1 = Monday,..., 6 = Saturday
    $newData[$dayOfWeek] += $row['count']; // суммируем количество заявок для каждого дня недели
}

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'В работе' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['Date_by'];
    $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 1 = Monday,..., 6 = Saturday
    $inWorkData[$dayOfWeek] += $row['count']; // суммируем количество заявок для каждого дня недели
}

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'Приостановлено' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['Date_by'];
    $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 1 = Monday,..., 6 = Saturday
    $pausedData[$dayOfWeek] += $row['count']; // суммируем количество заявок для каждого дня недели
}

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'Завершено' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['Date_by'];
    $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 1 = Monday,..., 6 = Saturday
    $completedData[$dayOfWeek] += $row['count']; // суммируем количество заявок для каждого дня недели
}

// Возвращаем новые данные в формате JSON
echo json_encode(
    array(
        'newData' => $newData,
        'inWorkData' => $inWorkData,
        'pausedData' => $pausedData,
        'completedData' => $completedData
    )
);
?>