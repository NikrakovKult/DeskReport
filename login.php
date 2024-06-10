<?php
// Установка соединения с базой данных
$conn = new mysqli("localhost", "root", "", "DeskReport");

// Обработка формы авторизации


// Обработка формы авторизации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["login"]) && isset($_POST["password"])) {
        $login = $_POST["login"];
        $password = $_POST["password"];

        // Проверка логина и пароля в базе данных
        $query = "SELECT * FROM users WHERE Login =?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["login"]) && isset($_POST["password"])) {
                $login = $_POST["login"];
                $password = $_POST["password"];

                // Проверка логина и пароля в базе данных
                $query = "SELECT * FROM users WHERE Login =?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user_data = $result->fetch_assoc();
                    $hashed_password = $user_data['Password'];
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Проверка пароля с помощью password_verify()
                    if (password_verify($password, $hashed_password)) {
                        // Авторизация успешна, создаем сессию
                        session_start();
                        $_SESSION["login"] = $login;
                        $_SESSION["id"] = $user_data['id'];
                        header('Location: Main.php'); // перенаправляем на страницу index.php
                        exit;
                    } else {
                        // Авторизация неудачна, выводим ошибку
                        $error = 'Неправильный логин или пароль!';
                    }
                } else {
                    // Логин не найден, выводим ошибку
                    $error = 'Логин не найден!';
                }
            }
        }
    }
}

// Recover password form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Проверка существования пользователя с таким email
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Генерация токена для восстановления пароля
        $token = bin2hex(random_bytes(16));

        // Обновление токена в базе данных
        $query = "UPDATE users SET token = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Отправка email с ссылкой для восстановления пароля
        $subject = "Восстановление пароля";
        $message = '<html>
  <head>
      <title>Восстановление пароля</title>
      <style>
          body {
              
              font-family: Arial, sans-serif;
              background:url(https://images.unsplash.com/photo-1620641788421-7a1c342ea42e?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D);
              background-position: center;
              background-size: cover;
          }
         .container {
           
            height:250px;
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            backdrop-filter:blur(15px);
            backgrond:none
          }
         .title {
           text-align:center;
           color:white;
              font-size: 24px;
              font-weight: bold;
              margin-bottom: 10px;
          }
         .message {
           color:white;
              font-size: 18px;
              color: #white;
              margin-bottom: 20px;
          }
         .button {
            width: 160px;
            display:flex;
            margin:0 auto;
            background-color: blue;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
          }
         .button:hover {
              background-color: darkblue;
          }
      </style>
  </head>
  <body>
      <div class="container">
      <h2 class="title">Восстановление пароля</h2>
      <p class="message">Для восстановления пароля перейдите по ссылке:</p>
      <a href="http://DeskPlusReport/RecoverPass/reset_password.php?token=' . $token . '" class="button">Восстановить пароль</a>
      <p class="message">Если вы не запрашивали восстановление пароля, проигнорируйте это письмо.</p>
      </div>
  </body>
  </html>';
        $headers = "From: DeskPlusReport@yandex.ru\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n"; // добавьте это
        mail($email, $subject, $message, $headers);


        
    } else {
        $error = 'Пользователь с таким email не найден.';
    }
}

// Закрытие соединения с базой данных
$conn->close();
?>

<!-- HTML-форма авторизации -->
<!DOCTYPE html>
<html lang="en">

<head>
    <title>DeskReport</title>
    <script src="LoginFunc.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <link rel="stylesheet" href="Styles/LoginCSS.css">

</head>

<body class="login_body">
    <div class="container">
        <div class="form-box">
            <div class="form-value">
                <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <h2>Авторизация</h2>
                    <div class="inputbox">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <input type="text" name="login" required>
                        <label for="">Логин</label>
                    </div>
                    <div class="inputbox">
                        <input type="password" name="password" required id="password">
                        <label for="">Пароль</label>
                        <ion-icon name="eye-outline" class="show-password-icon" onclick="showPassword(this)"></ion-icon>
                    </div>
                    <button type="submit">Войти</button>
                    <div class="bottom">
                        <div class="right">
                            <label><a href="#" onclick="OpenMailSend(event)">Забыли пароль?</a></label>
                        </div>
                    </div>
                    <?php if (isset($error)): ?>
                        <p id="error-message" style="color: red; text-align:center;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="Recover">
        <div class="form-box-recover">
            <div class="form-value-recover">
                <form class='forgot-password-form' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                    method="post">
                    <h2>Восстановление пароля</h2>
                    <h3>Введите вашу почту для восстановления пароля.</h3>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="text" name="email" required>
                        <label for="email">Email:</label>
                    </div>
                    <button type="submit" onclick="sendFormAndShowModal()">Восстановить пароль</button>
                    <div class="bottom">
                        <div class="right">
                            <label><a href="#" onclick="OpenLogin(event)">Назад</a></label>
                        </div>
                    </div>
                    <?php if (isset($error)): ?>
                        <p id="error-message" style="color: red; text-align:center;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>