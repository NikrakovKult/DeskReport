<?php
require_once ('connect.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Активность Детали</title>
    <link rel="stylesheet" href="Styles/active_details.css">
</head>

<body>
    <div class="info-container">

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="Active">
                <?php
                // Connection to database
                $conn = mysqli_connect("localhost", "root", "", "DeskReport");

                // Check connection
                if (!$conn) {
                    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
                }

                // Get active ID from GET request
                $id = $_GET['id'];

                // Select active data
                $result = mysqli_query($conn, "SELECT * FROM actives WHERE id = '$id'");

                // Display form with active details
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                    echo "<div class='form-group'>";
                    echo "<label for='name'>Название:</label>";
                    echo "<input type='text' id='Name' name='Name' value='" . $row['name'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='type'>Тип:</label>";
                    echo "<input type='text' id='Type' name='Type' value='" . $row['type'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='model'>Модель:</label>";
                    echo "<input type='text' id='Model' name='Model' value='" . $row['model'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='serial_number'>Серийный номер:</label>";
                    echo "<input type='text' id='Serial_number' name='Serial_number' value='" . $row['serial_number'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='purchase_date'>Дата покупки:</label>";
                    echo "<input type='text' id='Purchase_date' name='Purchase_date' value='" . $row['purchase_date'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='Location'>Местоположение:</label>";
                    echo "<input type='text' id='Location' name='Location' value='" . $row['location'] . "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='status'>Статус:</label>";
                    echo "<select id='status' name='Status'>";
                    echo "<option value='Активен' " . ($row['status'] == 'Активен' ? 'elected' : '') . ">Активен</option>";
                    echo "<option value='Неактивен' " . ($row['status'] == 'Неактивен' ? 'elected' : '') . ">Неактивен</option>";
                    echo "</select>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='notes'>Примечания:</label>";
                    echo "<textarea id='Notes' name='Notes'>" . $row['notes'] . "</textarea>";
                    echo "</div>";

                    echo "<input type='submit' value='Сохранить'>";
                }
                ?>
            </div>

        </form>
    </div>
    <?php
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $Name = $_POST['Name'];
        $Type = $_POST['Type'];
        $Model = $_POST['Model'];
        $Serial_number = $_POST['Serial_number'];
        $Purchase_date = $_POST['Purchase_date'];
        $Location = $_POST['Location'];
        $Status = $_POST['Status'];
        $Notes = $_POST['Notes'];
        $conn = mysqli_connect("localhost", "root", "", "DeskReport");
        if (!$conn) {
            die("Ошибка подключения к базе данных: " . mysqli_connect_error());
        }

        $query = "UPDATE actives SET Name = '$Name', Type = '$Type', Model = '$Model', Serial_number = '$Serial_number', Purchase_date = '$Purchase_date', Location = '$Location', Status = '$Status', Notes = '$Notes' WHERE id = '$id'";

        if (mysqli_query($conn, $query)) {
            echo "Активность обновлена успешно";
            header("Location: Main.php");
            exit;
        } else {
            echo "Ошибка обновления активности: " . mysqli_error($conn);
        }

        mysqli_close($conn);
    }
    ?>
</body>

</html>