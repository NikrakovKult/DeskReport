<?php
require_once ('connect.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
<link type="image/png" sizes="16x16" rel="icon" href="/icons8-модуль-16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подробности заявки</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="Styles/order_details.css">
</head>

<body>

    <div class="info-container">
        <script>
            document.addEventListener('click', function (event) {
                if (event.target.matches('.fullscreen-image-link')) {
                    event.preventDefault();
                    const imageUrl = event.target.dataset.image;
                    const fullscreenImage = document.getElementById('fullscreen-image');
                    fullscreenImage.querySelector('img').src = imageUrl;
                    fullscreenImage.style.display = 'flex';
                }
            });

            document.getElementById('fullscreen-image').addEventListener('click', function () {
                this.style.display = 'none';
            });
        </script>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="Order">
                <?php
                // Connection to database
                $conn = mysqli_connect("localhost", "root", "", "DeskReport");

                // Check connection
                if (!$conn) {
                    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
                }

                // Get order ID from GET request
                $id = $_GET['id'];

                // Define $sender_info as an empty string
                $sender_info = '';

                // Select order data
                $result = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$id'");

                // Display form with order details
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                    echo "<div class='form-group'>";
                    echo "<label for='id'>ID:</label>";
                    echo "<input type='text' id='id' name='id' value='" . $row['id'] . "' readonly>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='Discrip'>Описание:</label>";
                    echo "<input type='text' id='Discrip' name='Discrip' value='" . $row['Discrip'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='Sender'>Отправитель:</label>";
                    echo "<input type='text' id='Sender' name='Sender' value='" . $row['Sender'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='Specialist'>Спецаиалист:</label>";
                    echo "<select id='Specialist' name='Specialist'>";

                    $result_specialist = mysqli_query($conn, "SELECT username FROM users");
                    while ($row_specialist = mysqli_fetch_assoc($result_specialist)) {
                        echo "<option value='" . $row_specialist['username'] . "'>" . $row_specialist['username'] . "</option>";
                    }
                    echo "</select>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='Date_by'>Дата создания:</label>";
                    echo "<input type='text' id='Date_by' name='Date_by' value='" . $row['Date_by'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='Status'>Статус:</label>";
                    echo "<select id='Status' name='Status'>";
                    echo "<option value='Новая' " . ($row['Status'] == 'Новая' ? 'selected' : '') . ">Новая</option>";
                    echo "<option value='В работе' " . ($row['Status'] == 'В работе' ? 'selected' : '') . ">В работе</option>";
                    echo "<option value='Приостановлено' " . ($row['Status'] == 'Приостановлено' ? 'selected' : '') . ">Приостановлено</option>";
                    echo "<option value='Завершено' " . ($row['Status'] == 'Завершено' ? 'selected' : '') . ">Завершено</option>";
                    echo "</select>";
                    echo "</div>";

                    echo "<div class='photos'>";
                    echo "<div class='form-group'>";
                    echo "<a href='#' class='fullscreen-image-link' data-image='" . $row['Photo1'] . "'>";
                    echo "<img src='" . $row['Photo1'] . "' alt='Photo 1'>";
                    echo "</a>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<a href='#' class='fullscreen-image-link' data-image='" . $row['Photo2'] . "'>";
                    echo "<img src='" . $row['Photo2'] . "' alt='Photo 2'>";
                    echo "</a>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<a href='#' class='fullscreen-image-link' data-image='" . $row['Photo3'] . "'>";
                    echo "<img src='" . $row['Photo3'] . "' alt='Photo 3'>";
                    echo "</a>";
                    echo "</div>";
                    echo "</div>";


                    $sender_username = $row['Sender'];
                    $result_sender = mysqli_query($conn, "SELECT * FROM clients WHERE fio = '$sender_username'");
                    $sender_data = mysqli_fetch_assoc($result_sender);
                    $sender_info = ''; // Define $sender_info here
                    if ($sender_data !== null) {
                        $sender_info .= "<p>Имя: " . $sender_data['fio'] . "</p>";
                        $sender_info .= "<p>Email: " . $sender_data['email'] . "</p>";
                        $sender_info .= "<p>Отдел: " . $sender_data['otdel'] . "</p>";
                        $sender_info .= "<p>Должность: " . $sender_data['doljnost'] . "</p>";
                        $sender_info .= "<p>Телефон: " . $sender_data['mobile'] . "</p>";
                    } else {
                        $sender_info = "No sender data found.";
                    }

                    $specialist_username = $row['Specialist'];
                    $result_specialist = mysqli_query($conn, "SELECT * FROM users WHERE username = '$specialist_username'");
                    $specialist_data = mysqli_fetch_assoc($result_specialist);
                    $specialist_info = ''; // Define $specialist_info here
                    if ($specialist_data !== null) {
                        $specialist_info .= "<p>Имя: " . $specialist_data['username'] . "</p>";
                        $specialist_info .= "<p>Email: " . $specialist_data['email'] . "</p>";
                        $specialist_info .= "<p>Должность: " . $specialist_data['Doljnost'] . "</p>";
                        $specialist_info .= "<p>Отдел: " . $specialist_data['Otdel'] . "</p>";
                        $specialist_info .= "<p>Мобильный телефон: " . $specialist_data['Mobile'] . "</p>";
                    } else {
                        $specialist_info = "<p>Спецаилист не назначен.</p>";
                    }

                    echo "<input type='submit' value='Сохранить'>";
                }
                ?>
            </div>

        </form>
        <div class="specialist-info">
            <h2>Информация о специалисте</h2>
            <?php echo $specialist_info; ?>
        </div>
        <div class="sender-info">
            <h2>Информация об отправителе</h2>
            <?php echo $sender_info; ?>
        </div>
    </div>
    <?php
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $Discrip = $_POST['Discrip'];
        $Sender = $_POST['Sender'];
        $Specialist = $_POST['Specialist'];
        $Date_by = $_POST['Date_by'];
        $Status = $_POST['Status'];
        $conn = mysqli_connect("localhost", "root", "", "DeskReport");

        if (!$conn) {
            die("Ошибка подключения к базе данных: " . mysqli_connect_error());
        }

        $query = "UPDATE orders SET Discrip = '$Discrip', Sender = '$Sender', Specialist = '$Specialist', Date_by = '$Date_by', Status = '$Status' WHERE id = '$id'";

        if (mysqli_query($conn, $query)) {
            echo "Order updated successfully";
            header("Location: Main.php");
            exit;
        } else {
            echo "Error updating order: " . mysqli_error($conn);
        }

        mysqli_close($conn);
    }
    if (isset($_GET['action']) && $_GET['action'] == 'add') {
        // Создаем новую заявку
        $sql = "INSERT INTO orders (Discrip, Sender, Specialist, Date_by, Status, Photo1, Photo2, Photo3) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $discrip, $sender, $specialist, $date_by, $status);
        $discrip = '';
        $sender = '';
        $specialist = '';
        $date_by = date('Y-m-d H:i:s');
        $status = 'Новая';
        $stmt->execute();
        header('Location: Main.php');
        exit;
    }
    ?>

</body>

</html>