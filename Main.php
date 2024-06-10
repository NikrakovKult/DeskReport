<?php
session_start();
require_once ('connect.php');

// Check if the user is logged in
if (isset($_POST['login']) && isset($_POST['password'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Query to check if the user exists
    $sql = "SELECT * FROM users WHERE Login =? AND Password =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION["login"] = $login;
        $_SESSION["id"] = $user_data['id'];
        if (isset($user_data['username'])) {
            $_SESSION["username"] = $user_data['username'];
        } else {
            $_SESSION["username"] = ''; // или некоторое значение по умолчанию
        }
        if (isset($user_data['Role'])) {
            $_SESSION["role"] = $user_data['Role'];
        } else {
            $_SESSION["role"] = ''; // или некоторое значение по умолчанию
        }
        // Redirect to the main page
        header('Location: Main.php');
        exit;
    }
}

// Check if the user is already logged in
if (isset($_SESSION["id"])) {
    $query = "SELECT id, username, doljnost, otdel FROM users WHERE id =?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION["id"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        // Теперь вы можете получить доступ к ключу id массива $user_data
        $id = $user_data['id'];
    } else {
        echo "Данные не найдены";
        exit;
    }
} else {
    echo "Вы не авторизованы";
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeskReport</title>
    <link rel="stylesheet" href="/Styles/Main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="MainFunc.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
        integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
</head>

<body>
    <div class="nav-sidebar">
        <nav class="nav">
            <a href="#" class="nav-item" active-color="blue" onclick="showOrdersTable()">Заявки</a>

            <a href="#" class="nav-item" active-color="blue" onclick="showActivesTable()">Активы</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-address-book'></i>Адресная книга
                Сотрудники</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-address-card'></i>Адресная книга
                клиенты</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-sticky-note'></i>Заметки</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-tasks'></i>Задачи</a>
            <span class="nav-indicator"></span>
        </nav>
    </div>
    <div class="search-block">
        <input type="text" id="search-input" placeholder="Поиск по таблице">
        <select name="status" id="status" onchange="updateTable(this.value)">
            <option value="Все заявки">Все заявки</option>
            <option value="Новая">Новая</option>
            <option value="В работе">В работе</option>
            <option value="Приостановлено">Приостановлено</option>
            <option value="Завершено">Завершено</option>
        </select>

    </div>
    <div class="user-info">
        <p> <?php echo $user_data['username']; ?></p>
        <p><a href="Logout.php"><i class="fa fa-sign-out-alt" aria-hidden="true"></i></a></p>
    </div>

    <div class="main-table">

        <?php

        if (isset($_SESSION['id'])) {
            $sql = "SELECT * FROM orders";
            if (isset($_POST['searchQuery'])) {
                $searchQuery = $_POST['searchQuery'];
                $sql = "SELECT * FROM orders WHERE Discrip LIKE? OR Sender LIKE? OR Specialist LIKE?";
                $stmt = $conn->prepare($sql);
                $param1 = "%$searchQuery%";
                $param2 = "%$searchQuery%";
                $param3 = "%$searchQuery%";
                $stmt->bind_param("sss", $param1, $param2, $param3);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            if (isset($_POST['status'])) {
                $status = $_POST['status'];
                $sql = "SELECT * FROM orders WHERE Status =?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $status);
                $stmt->execute();
                $result = $stmt->get_result();
                if (!empty($sql)) {
                    $sql .= " AND Status = '$status'";
                } else {
                    $sql .= " WHERE Status = '$status'";
                }
            }
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
                echo "<td><a href='order_details.php?id=" . $row['id'] . "'>" . $row['id'] . "</a></td>";
                echo "<td><a href='order_details.php?id=" . $row['id'] . "'>" . $row['Discrip'] . "</a></td>";
                echo "<td><a href='order_details.php?id=" . $row['id'] . "'>" . $row['Sender'] . "</a></td>";
                echo "<td><a href='order_details.php?id=" . $row['id'] . "'>" . $row['Specialist'] . "</a></td>";
                echo "<td><a href='order_details.php?id=" . $row['id'] . "'>" . $row['Date_by'] . "</a></td>";
                echo "<td><a href='order_details.php?id=" . $row['id'] . "'>" . $row['Status'] . "</a></td>";

                echo "</tr>";

            }

            echo "</table>";
        }
        ?>
        <?php
        if (isset($_SESSION['id'])) {
            $sql = "SELECT * FROM actives";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            // Создание таблицы
            echo "<table border='0' id='actives-table' ";
            echo "<thead>
          <tr id='head'>
            <th>ID</th>
            <th>Имя</th>
            <th>Тип</th>
            <th>Модель</th>
            <th>Серийный номер</th>
            <th>Дата покупки</th>
            <th>Местоположение</th>
            <th>Статус</th>
            <th>Примечания</th>
          </tr>
        </thead>";

            // Вывод данных из таблицы
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['id'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['name'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['type'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['model'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['serial_number'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['purchase_date'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['location'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['status'] . "</a></td>";
                echo "<td><a href='active_details.php?id=" . $row['id'] . "'>" . $row['notes'] . "</a></td>";
                echo "</tr>";
            }

            echo "</table>";
        }
        ?>
    </div>



</body>

</html>