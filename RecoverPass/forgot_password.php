<?php
// Подключение к базе данных
$con = new mysqli("localhost", "root", "", "DeskReport");

// Проверка соединения
if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);
}

// Получение email из формы
$email = $_POST['email'];

// Проверка существования пользователя с таким email
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  // Генерация токена для восстановления пароля
  $token = bin2hex(random_bytes(16));

  // Обновление токена в базе данных
  $query = "UPDATE users SET token = ? WHERE email = ?";
  $stmt = $con->prepare($query);
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
      <a href="http://DeskPlusReport/RecoverPass/reset_password.php?token='. $token. '" class="button">Восстановить пароль</a>
      <p class="message">Если вы не запрашивали восстановление пароля, проигнорируйте это письмо.</p>
      </div>
  </body>
  </html>';
  $headers = "From: DeskPlusReport@yandex.ru\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n"; // добавьте это
  mail($email, $subject, $message, $headers);

  echo "Инструкции для восстановления пароля отправлены на ваш email.";
} else {
  echo "Пользователь с таким email не найден.";
}

// Закрытие соединения с базой данных
$con->close();
?>