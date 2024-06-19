<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeskReport</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
            flex-direction: column;
            background: url("https://img.goodfon.ru/wallpaper/nbig/f/b7/abstraktsiia-smes-tsvetov-perelivy-abstraction-a-mixture-of.webp");
            background-position: center;
            background-size: cover;
            backdrop-filter: blur(15px);
            color: white;
        }

        .container {
            width: 80%;
            max-width: 600px;
            background-color: #fff;
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #3cd1d1;
            text-align: center;
            margin-bottom: 1rem;
        }

        p {
            border-radius: 15px;
            padding: 15px;
            background: #0000007d;
            margin-bottom: 0.5rem;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        button {
            background-color: #3cd1d1;
            ;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #35c7c785;
        }

        .error,
        .success {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .success {
            background-color: #4CAF50;
        }

        ul {
            border-radius: 15px;

            background: #0000007d;
            list-style: none;
            padding: 15px;
            margin-top: 2rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 600px) {
            .container {
                padding: 1rem;
            }

            button {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }

        #phone {
            padding: 5px;
            color: white;
            background: #0000007d;
            border-radius: 15px;
        }

        #back-to-glav {
            margin: 0 auto;
            display: block;
        }
        a{
            text-decoration: none;
            color: white;
        }
    </style>
</head>

<body>
    <?php
    // Connect to database
    $conn = mysqli_connect('localhost', 'root', '', 'DeskReport');

    if (!$conn) {
        die('Connection failed: ' . mysqli_connect_error());
    }

    // Check if form has been submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $phone = $_POST['phone'];

        // Query database to retrieve client data
        $stmt = mysqli_prepare($conn, "SELECT * FROM clients WHERE mobile =?");
        mysqli_stmt_bind_param($stmt, "s", $phone);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            // Client found, authenticate and display main page
            $client_data = mysqli_fetch_assoc($result);
            $fio = $client_data['fio'];
            $email = $client_data['email'];
            $mobile = $client_data['mobile'];
            $otdel = $client_data['otdel'];
            $doljnost = $client_data['doljnost'];

            // Display main page
            ?>
            <h1>Страница заявок</h1>
            <p>Здравствуйте, <?= $fio; ?>!</p>
            <p>Почта: <?= $email; ?></p>
            <p>Телефон <?= $mobile; ?></p>
            <p>Отдел: <?= $otdel; ?></p>
            <p>Должность: <?= $doljnost; ?></p>

            <!-- Add buttons for new request and my requests -->


            <!-- Display client's requests -->
            <h2>Мои заявки:</h2>
            <ul>
                <?php
                $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE Sender =?");
                mysqli_stmt_bind_param($stmt, "s", $fio);
                mysqli_stmt_execute($stmt);
                $requests_result = mysqli_stmt_get_result($stmt);
                while ($request = mysqli_fetch_assoc($requests_result)) {
                    ?>
                    <li>
                        (Заявка#<?= $request['id']; ?>) - <?= $request['Discrip']; ?> --- <?= $request['Status']; ?>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <?php
        } else {
            // Client not found, display error message
            $error = 'Client not found. Please register.';
            ?>
            <p><?= $error; ?></p>
            <?php
        }
    }

    // Register new client
    if (isset($_POST['register'])) {
        $fio = $_POST['fio'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $otdel = $_POST['otdel'];
        $doljnost = $_POST['doljnost'];

        $stmt = mysqli_prepare($conn, "INSERT INTO clients (fio, email, mobile, otdel, doljnost) VALUES (?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssss", $fio, $email, $mobile, $otdel, $doljnost);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        ?>
        <p>You have been registered successfully!</p>
        <?php
    }

    // Create new request
    if (isset($_POST['new_request'])) {
        $discrip = $_POST['discrip'];
        $fio = $_POST['fio'];

        $stmt = mysqli_prepare($conn, "INSERT INTO orders (Discrip, Sender, Specialist, Date_by, Status) VALUES (?,?, 'Не назначенно', NOW(), 'Новая')");
        mysqli_stmt_bind_param($stmt, "sss", $discrip, $fio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        ?>
        <p>Your request has been created successfully!</p>
        <?php
    }

    // Display my requests
    if (isset($_POST['my_requests'])) {
        $fio = $_POST['fio'];

        $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE Sender =?");
        mysqli_stmt_bind_param($stmt, "s", $fio);
        mysqli_stmt_execute($stmt);
        $requests_result = mysqli_stmt_get_result($stmt);

        ?>
        <h2>Мои заявки:</h2>
        <ul>
            <?php
            while ($request = mysqli_fetch_assoc($requests_result)) {
                ?>
                <li>
                    Заявка #<?= $request['id']; ?> - <?= $request['Discrip']; ?> (<?= $request['Status']; ?>)
                </li>
                <?php
            }
            ?>
        </ul>
        <?php
    }

    mysqli_close($conn);
    ?>

    <!-- HTML form for login -->
    <form method="post">
        <label for="phone">Введите ваш телефон для просмотра заявок:</label>
        <input type="text" name="phone" id="phone" required>
        <button type="submit">Найти</button>
        <br>
        

    </form>
    <button type="submit"  id="back-to-glav"><a href="http://deskplusreport/oneschool-master/" id="back-to-glav">Назад на главную</a></button>
</body>

</html>