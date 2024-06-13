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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
        integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
</head>

<body>
    <div class="info">
        <h2 id="header"></h2>
        <p id="datetime"></p>
        <p id="weather">
            <img id="weather-icon" src="" alt="Weather icon">
            <span id="weather-text"></span>
        </p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const headerElement = document.getElementById('header');
            const datetimeElement = document.getElementById('datetime');
            const weatherElement = document.getElementById('weather');
            const weatherIconElement = document.getElementById('weather-icon');
            const weatherTextElement = document.getElementById('weather-text');

            if (!headerElement || !datetimeElement || !weatherElement || !weatherIconElement || !weatherTextElement) {
                console.error('Elements not found');
            } else {
                headerElement.textContent = 'DeskPlusReport';

                function updateDateTime() {
                    console.log('Updating datetime');
                    const now = new Date();
                    const dayOfWeek = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'][now.getDay()];
                    const datetimeString = `${dayOfWeek}, ${now.toLocaleDateString()} ${now.toLocaleTimeString()}`;
                    console.log(datetimeString);
                    datetimeElement.textContent = datetimeString;
                }

                function updateWeather() {
                    console.log('Updating weather');
                    const apiKey = '396fe9f178d41a59cfcdfa26642db76e'; // замените на свой API ключ
                    const city = 'Nizhny Tagil'; // замените на свой город
                    const url = `http://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            const weatherIconUrl = `http://openweathermap.org/img/w/${data.weather[0].icon}.png`;
                            weatherIconElement.src = weatherIconUrl;
                            const weatherString = `Погода: ${data.weather[0].description}, температура: ${data.main.temp}°C`;
                            console.log(weatherString);
                            weatherTextElement.textContent = weatherString;
                        })
                        .catch(error => console.error('Error fetching weather data:', error));
                }

                updateDateTime();
                updateWeather();
                setInterval(updateDateTime, 1000);
                setInterval(updateWeather, 60000); // обновляем погоду каждые 60 секунд
            }
        });
    </script>
    <div class="nav-sidebar">
        <nav class="nav">
            <a href="#" class="nav-item" active-color="blue" onclick="showOrdersTable()">Заявки</a>

            <a href="#" class="nav-item" active-color="blue" onclick="showActivesTable()">Активы</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-address-book'></i>Адресная книга
                Сотрудники</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-address-card'></i>Адресная книга
                клиенты</a>
            <a href="#" class="nav-item" active-color="blue" onclick="showZametki()"><i
                    class='fa fa-sticky-note'></i>Заметки</a>
            <a href="#" class="nav-item" active-color="blue"><i class='fa fa-tasks'></i>Задачи</a>
            <a href="#" class="nav-item" active-color="blue" onclick="showGraph()"><i
                    class="fa fa-bar-chart"></i>Анализ</a>
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
        <select name="specialist" id="specialist" onchange="updateTable(this.value)">
            <option value="">Все специалисты</option>
            <option value="<?php echo $user_data['username']; ?>">Мои заявки</option>
        </select>
        <button id="update-button">Обновить таблицу</button>
        <select id="update-interval">
            <option value="0">Не обновлять</option>
            <option value="10">Каждые 10 секунд</option>
            <option value="20">Каждые 20 секунд</option>
            <option value="60">Каждую 1 минуту</option>
        </select>
        <button id="add-button" "><a href="NewOrder.php">Добавить</a></button>
    </div>
    <div class="user-info">
        <p> <?php echo $user_data['username']; ?></p>
        <p><a href="Logout.php"><i class="fa fa-sign-out-alt" aria-hidden="true"></i></a></p>
    </div>

    <div class="main-table">

        <?php

        if (isset($_SESSION['id'])) {
            $sql = "SELECT * FROM orders ORDER BY Date_by DESC";
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
                echo "<td class='" . getStatusClass($row['Status']) . "'>" . $row['Status'] . "</td>";

                echo "</tr>";

            }

            echo "</table>";
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

        <?php
        if (isset($_SESSION['id'])) {
            $sql = "SELECT * FROM actives";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            // Создание таблицы
            echo "<table border='0' id='actives-table'  ";
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

        <div class="graph">
            <div style="width: 20%; color:white; margin:10px;">
                <h2>Новые заявки</h2>
                <canvas id="new-chart"></canvas>
            </div>
            <div style="width: 20%; color:white; margin:10px;">
                <h2>Заявки в работе</h2>
                <canvas id="in-work-chart"></canvas>
            </div>
            <div style="width: 20%; color:white; margin:10px;">
                <h2>Приостановленные заявки</h2>
                <canvas id="paused-chart"></canvas>
            </div>
            <div style="width: 20%; color:white; margin:10px;">
                <h2>Завершенные заявки</h2>
                <canvas id="completed-chart"></canvas>
            </div>

            <?PHP

            $monday = date('Y-m-d', strtotime('monday this week'));
            $sunday = date('Y-m-d', strtotime('sunday this week'));

            $daysOfWeek = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
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
            $data = array(
                'newData' => $newData,
                'inWorkData' => $inWorkData,
                'pausedData' => $pausedData,
                'completedData' => $completedData,

            );


            ?>

            <script>
                const ctxNew = document.getElementById('new-chart').getContext('2d');
                const chartNew = new Chart(ctxNew, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($daysOfWeek) ?>,
                        datasets: [{
                            label: 'Количество заявок',
                            data: <?= json_encode($newData) ?>,
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            borderColor: 'rgba(255, 255, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                const ctxInWork = document.getElementById('in-work-chart').getContext('2d');
                const chartInWork = new Chart(ctxInWork, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($daysOfWeek) ?>,
                        datasets: [{
                            label: 'Количество заявок',
                            data: <?= json_encode($inWorkData) ?>,
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            borderColor: 'rgba(255, 255, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                const ctxPaused = document.getElementById('paused-chart').getContext('2d');
                const chartPaused = new Chart(ctxPaused, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($daysOfWeek) ?>,
                        datasets: [{
                            label: 'Количество заявок',
                            data: <?= json_encode($pausedData) ?>,
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            borderColor: 'rgba(255, 255, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                const ctxCompleted = document.getElementById('completed-chart').getContext('2d');
                const chartCompleted = new Chart(ctxCompleted, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($daysOfWeek) ?>,
                        datasets: [{
                            label: 'Количество заявок',
                            data: <?= json_encode($completedData) ?>,
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            borderColor: 'rgba(255, 255, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });




                function updateCharts() {
    // Отправляем запрос на сервер для получения новых данных
    fetch('get_new_data.php')
       .then(response => response.json())
       .then(data => {
            // Обновляем данные графиков
            chartNew.data.datasets[0].data = data.newData;
            chartInWork.data.datasets[0].data = data.inWorkData;
            chartPaused.data.datasets[0].data = data.pausedData;
            chartCompleted.data.datasets[0].data = data.completedData;

            // Обновляем графики
            chartNew.update();
            chartInWork.update();
            chartPaused.update();
            chartCompleted.update();
        });
}

// Вызываем функцию updateCharts каждые 10 секунд
setInterval(updateCharts, 10000);

                //...

                // Обновляем графики

            </script>
        </div>


    </div>



    </div>



</body>

</html>