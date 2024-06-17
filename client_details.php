<?php
require_once ('connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Details</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="Styles/client_details.css">
</head>
<body>

<div class="info-container">
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <div class="Client">
            <?php
            // Check if id is set in GET request
            if (isset($_GET['id'])) {
                $id = $_GET['id'];

                // Connection to database
                $conn = mysqli_connect("localhost", "root", "", "DeskReport");

                // Check connection
                if (!$conn) {
                    die("Connection failed: ". mysqli_connect_error());
                }

                // Select client data
                $result = mysqli_query($conn, "SELECT * FROM clients WHERE id = '$id'");

                // Display form with client details
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<input type='hidden' name='id' value='". $row['id']. "'>";
                    echo "<div class='form-group'>";
                    echo "<label for='fio'>FIO:</label>";
                    echo "<input type='text' id='fio' name='fio' value='". $row['fio']. "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='email'>Email:</label>";
                    echo "<input type='text' id='email' name='email' value='". $row['email']. "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='mobile'>Mobile:</label>";
                    echo "<input type='text' id='mobile' name='mobile' value='". $row['mobile']. "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='otdel'>Отдел:</label>";
                    echo "<input type='text' id='otdel' name='otdel' value='". $row['otdel']. "'>";
                    echo "</div>";

                    echo "<div class='form-group'>";
                    echo "<label for='doljnost'>Должность:</label>";
                    echo "<input type='text' id='doljnost' name='doljnost' value='". $row['doljnost']. "'>";
                    echo "</div>";

                    echo "<input type='submit' value='Сохранить'>";
                }
            } else {
                echo "No client ID provided";
            }
           ?>

            <?php
            // Update client data when form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $id = $_POST['id'];
                $fio = $_POST['fio'];
                $email = $_POST['email'];
                $mobile = $_POST['mobile'];
                $otdel = $_POST['otdel'];
                $doljnost = $_POST['doljnost'];

                $conn = mysqli_connect("localhost", "root", "", "DeskReport");
                $query = "UPDATE clients SET fio = '$fio', email = '$email', mobile = '$mobile', otdel = '$otdel', doljnost = '$doljnost' WHERE id = '$id'";
                mysqli_query($conn, $query);

                // Redirect to Main.php
                header("Location: Main.php");
                exit;
            }
           ?>
        </div>
    </form>
</div>

</body>
</html>