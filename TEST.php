
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<style>
    tr {
        display: block;
        margin: 10px;
        border-radius: 15px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 10px;
    }

    tr:hover {
        background-color: #f5f5f5;
    }

    td {
        display: inline-block;
        width: 16%;
        margin-right: 2%;
        border: none;
        background-color: transparent;
        padding: 0;
    }

    tbody a{
        color: #0066cc;
        text-decoration: none;
        border: none;
        background-color: transparent;
        padding: 0;
    }
</style>
<?php
// Подключение к базе данных
$conn = mysqli_connect("localhost", "root", "", "DeskReport");

// Проверка подключения
if (!$conn) {
    die("Ошибка подключения: ". mysqli_connect_error());
}

// Выборка данных из таблицы actives
$sql = "SELECT * FROM actives";
$result = mysqli_query($conn, $sql);

// Проверка результата запроса
if (!$result) {
    die("Ошибка запроса: ". mysqli_error($conn));
}

// Вывод таблицы на страницу
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Название</th>";
echo "<th>Тип</th>";
echo "<th>Модель</th>";
echo "<th>Серийный номер</th>";
echo "<th>Дата покупки</th>";
echo "<th>Местоположение</th>";
echo "<th>Статус</th>";
echo "<th>Примечания</th>";
echo "</tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>". $row['id']. "</td>";
    echo "<td>". $row['name']. "</td>";
    echo "<td>". $row['type']. "</td>";
    echo "<td>". $row['model']. "</td>";
    echo "<td>". $row['serial_number']. "</td>";
    echo "<td>". $row['purchase_date']. "</td>";
    echo "<td>". $row['location']. "</td>";
    echo "<td>". $row['status']. "</td>";
    echo "<td>". $row['notes']. "</td>";
    echo "</tr>";
}

echo "</table>";

// Закрытие подключения
mysqli_close($conn);
?>
</body>
</html> 