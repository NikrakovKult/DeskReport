<?php
require_once('connect.php');
if (isset($_POST['update'])) {
    $sql = "SELECT * FROM orders ORDER BY Date_by DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
  
    // Создание таблицы
    echo "<table border='0' id='orders-table'>";
    echo "<thead>
      <tr id='head'>
        <th>ID</th>
        <th>Описание</th>
        <th>Отправитель</th>
        <th>Специалист</th>
        <th>Дата Создания</th>
        <th>Статус</th>
      </tr>
    </thead>";
  
    // Вывод данных из таблицы
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>";
      echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['id']. "</a></td>";
      echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Discrip']. "</a></td>";
      echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Sender']. "</a></td>";
      echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Specialist']. "</a></td>";
      echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Date_by']. "</a></td>";
      echo "<td class='". getStatusClass($row['Status']). "'>". $row['Status']. "</td>";
      echo "</tr>";
    }
  
    echo "</table>";
  }
if (isset($_POST['searchQuery']) && isset($_POST['status'])) {
    $searchQuery = $_POST['searchQuery'];
    $status = $_POST['status'];
    if ($status == 'Все заявки') {
        $sql = "SELECT * FROM orders WHERE Discrip LIKE? OR Sender LIKE? OR Specialist LIKE? ";
        $stmt = $conn->prepare($sql);
        $param1 = "%$searchQuery%";
        $param2 = "%$searchQuery%";
        $param3 = "%$searchQuery%";
        $stmt->bind_param("sss", $param1, $param2, $param3);
    } else {
        $sql = "SELECT * FROM orders WHERE (Discrip LIKE? OR Sender LIKE? OR Specialist LIKE?) AND Status =?";
        $stmt = $conn->prepare($sql);
        $param1 = "%$searchQuery%";
        $param2 = "%$searchQuery%";
        $param3 = "%$searchQuery%";
        $param4 = $status;
        $stmt->bind_param("ssss", $param1, $param2, $param3, $param4);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Создание таблицы
        echo "<table border='0'>";
        echo "<thead>
          <tr id='head'>
            <th>ID</th>
            <th>Описание</th>
            <th>Отправитель</th>
            <th>Специалист</th>
            <th>Дата Создания</th>
            <th>Статус</th>
          </tr>
        </thead>";

        // Вывод данных из таблицы
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['id']. "</a></td>";
            echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Discrip']. "</a></td>";
            echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Sender']. "</a></td>";
            echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Specialist']. "</a></td>";
            echo "<td><a href='order_details.php?id=". $row['id']. "'>". $row['Date_by']. "</a></td>";
            echo "<td class='" . getStatusClass($row['Status']) . "'>" . $row['Status'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "";
    }
} else {
    echo "";
}








function getStatusClass($status)
        {
            switch ($status) {
                case 'Новая':
                    return 'new-status';
                case 'В работе':
                    return 'in-work-status';
                case 'Приостановлено':
                    return 'paused-status';
                case 'Завершено':
                    return 'completed-status';
                default:
                    return '';
            }
        }
        
        
?>
