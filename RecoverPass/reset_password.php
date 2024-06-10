<?php
// Подключение к базе данных
$con = new mysqli("localhost", "root", "", "DeskReport");

// Проверка соединения
if ($con->connect_error) {
  die("Connection failed: ". $con->connect_error);
}

// Если это не POST-запрос, показываем форму
if (!isset($_POST['token'])) {
?>
  <!DOCTYPE html>
  <html lang="ru">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Styles/ResetPass.css">
    <script src="Func.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <title>Восстановление пароля</title>
  </head>
  <body>
    <div class="container">
      <div class="form-box">
        <div class="form-value">
        <form action="reset_password.php" method="post">
            <h2>Придумайте новый пароль</h2>
            <div class="inputbox">
              <input type="password" id="password" name="password" required>
              <label for="password">Новый пароль:</label>
              <ion-icon name="eye-outline" class="show-password-icon" onclick="showPassword(this, 'password')"></ion-icon>
            </div>
            <div class="inputbox">
              <input type="password" id="confirm_password" name="confirm_password" required>
              <label for="confirm_password">Подтверждение пароля:</label>
              <ion-icon name="eye-outline" class="show-password-icon" onclick="showPassword(this, 'confirm_password')"></ion-icon>
            </div>
            <input class="token" type="hidden" name="token" value="<?php echo $_GET['token'];?>">
            <button type="submit">Сбросить пароль</button>
            <p id="message" style="color: red;"></p>
          </form>
        </div>
      </div>
    </div>
  </body>
  </html>
  <script>
    function showPassword(icon, inputId) {
      var passwordInput = document.getElementById(inputId);
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.name = "eye-off-outline";
      } else {
        passwordInput.type = "password";
        icon.name = "eye-outline";
      }
    }
  </script>
<?php
} else {
  $token = $_POST['token'];
  $success = "";
  $error = "";

  // Проверка существования токена в базе данных
  $query = "SELECT * FROM users WHERE token =?";
  $stmt = $con->prepare($query);
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // Получение нового пароля из формы
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка на пустые поля
    if (empty($password) || empty($confirm_password)) {
        $error = "Поля пароля и подтверждения пароля不能为空.";
    } elseif ($password != $confirm_password) {
        $error = "Пароли не совпадают.";
    } else {
        // Получение текущего пароля из базы данных
        $query = "SELECT password FROM users WHERE token =?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_password = $result->fetch_assoc()['password'];

        // Проверка на совпадение с текущим паролем
        if (password_verify($password, $current_password)) {
            $error = "Пароль не должен совпадать с текущим!";
        } else {
            // Хеширование пароля
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Обновление пароля в базе данных
            $query = "UPDATE users SET password =?, token = '' WHERE token =?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $hashed_password, $token);

            if ($stmt->execute()) {
                $success = "Пароль успешно сброшен.";
            } else {
                $error = "Ошибка при смене пароля.";
            }
        }
    }
} else {
    $error = "Токен не найден.";
}

  // Выводим сообщение на странице
  if (!empty($success)) {
    echo "<script>
       var modal = document.createElement('div');
      modal.innerHTML = '".htmlspecialchars($success)."';
      modal.style.position = 'fixed';
      modal.style.top = '50%';
      modal.style.left = '50%';
      modal.style.transform = 'translate(-50%, -50%)';
      modal.style.background = 'white';
      modal.style.padding = '20px';
      modal.style.border = '1px solid #ccc';
      modal.style.borderRadius = '5px';
      modal.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
      document.body.appendChild(modal);
      setTimeout(function() {
        modal.remove();
        window.location.href = 'login.php';
      }, 5000);
    </script>";
  } elseif (!empty($error)) {
    echo "<script>document.getElementById('message').innerText = '".htmlspecialchars($error)."';</script>";
  }
}
$con->close();
?>