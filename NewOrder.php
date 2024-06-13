<?php
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "root", "", "DeskReport");

    // Проверка соединения
    if (!$conn) {
        die("Ошибка соединения: " . mysqli_connect_error());
    }

    // Проверка отправки формы
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Обработка данных формы
        $fio = $_POST['fio'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $otdel = $_POST['otdel'];
        $doljnost = $_POST['doljnost'];
        $discrip = $_POST['discrip'];

        // Вставка данных в базу данных
        $query = "SELECT * FROM clients WHERE fio = '$fio' AND email = '$email' AND mobile = '$mobile' AND otdel = '$otdel' AND doljnost = '$doljnost'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        // Если запись уже существует, то получаем ее ID
        $client_id = mysqli_fetch_assoc($result)['id'];
    } else {
        // Если записи не существует, то создаем новую
        $query = "INSERT INTO clients (fio, email, mobile, otdel, doljnost) VALUES ('$fio', '$email', '$mobile', '$otdel', '$doljnost')";
        mysqli_query($conn, $query);
        $client_id = mysqli_insert_id($conn);
    }

        $client_id = mysqli_insert_id($conn);

        $photo1 = $photo2 = $photo3 = '';
        if (!empty($_FILES['photo'])) {
            $files = $_FILES['photo'];
            $photo_paths = array();
            foreach ($files['name'] as $key => $value) {
                $tmp_name = $files['tmp_name'][$key];
                $name = $files['name'][$key];
                $upload_dir = 'uploads/';
                $upload_file = $upload_dir . $name;
                move_uploaded_file($tmp_name, $upload_file);
                $photo_paths[] = $upload_file;
            }
            $photo1 = $photo_paths[0];
            $photo2 = $photo_paths[1];
            $photo3 = $photo_paths[2];
        }

        $query = "INSERT INTO orders (Discrip, Sender, Specialist, Date_by, Status, Photo1, Photo2, Photo3) VALUES ('$discrip', '$fio', 'Не назначенно', NOW(), 'Новая', '$photo1', '$photo2', '$photo3')";
        mysqli_query($conn, $query);

        mysqli_close($conn);

        // Перенаправление на страницу благодарности
        // header('Location: Login.php');
        exit;
    }
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.5/inputmask.min.js"></script>
    <script src="Func.js"></script>
    <title>Заявка</title>
    <link rel="stylesheet" href="/Styles/NewOrder.css">
</head>

<body>
    <div class="container">
        <div class="form-box">
            <div class="form-value">
                <form action="" method="post" enctype="multipart/form-data">
                    <h2>Форма заявки</h2>
                    <div class="inputbox">
                        <input type="text" name="fio" required>
                        <label>ФИО:</label>
                        <ion-icon name="person"></ion-icon>
                    </div>

                    <div class="inputbox">
                        <input type="text" name="email" required>
                        <label>Почта:</label>
                        <ion-icon name="mail"></ion-icon>
                    </div>

                    <div class="inputbox">
                        <input type="text" name="mobile" id="mobile-input" required maxlength="11">
                        <label>Телефон:</label>
                        <ion-icon name="call"></ion-icon>
                    </div>

                    <div class="inputbox">
                        <input type="text" name="otdel" required>
                        <label>Отдел:</label>
                        <ion-icon name="business"></ion-icon>
                    </div>

                    <div class="inputbox">
                        <input type="text" name="doljnost" required>
                        <label>Должность:</label>
                        <ion-icon name="construct"></ion-icon>
                    </div>

                    <div class="textarea-box">
                        <label for="discrip">Опишите проблему:</label>
                        <textarea name="discrip" id="discrip" required maxlength="255"></textarea>
                        <div class="upload-box">
                            <label for="photo">Загрузите фото:</label>
                            <div class="drag-drop-zone">

                                <input type="file" id="photo" name="photo[]" accept="image/*" multiple>
                            </div>
                        </div>
                        <div class="upload-box">
                            <label for="photo">Загрузите фото:</label>
                            <div class="drag-drop-zone">

                                <input type="file" id="photo" name="photo[]" accept="image/*" multiple>
                            </div>
                        </div>
                        <div class="upload-box">
                            <label for="photo">Загрузите фото:</label>
                            <div class="drag-drop-zone">

                                <input type="file" id="photo" name="photo[]" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>

                    <div class="button-box">
                        <button type="submit" name="submit">Отправить заявку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>