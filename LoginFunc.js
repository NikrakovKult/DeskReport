function OpenMailSend(event) {
  const loginForm = document.querySelector(".container");
  const registerForm = document.querySelector(".Recover");

  loginForm.style.display = 'none';
  registerForm.style.display = 'flex';
  event.preventDefault(); // или return false;
}

function OpenLogin(event) {
  const loginForm = document.querySelector(".container");
  const registerForm = document.querySelector(".Recover");

  loginForm.style.display = 'flex';
  registerForm.style.display = 'none';
  event.preventDefault(); // или return false;
}

function showPassword(icon) {
  var passwordInput = document.getElementById("password");
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    icon.name = "eye-off-outline";
  } else {
    passwordInput.type = "password";
    icon.name = "eye-outline";
  }
}
function sendFormAndShowModal() {
  document.querySelector('.forgot-password-form').submit();
  var modal = document.createElement('div');
  modal.innerHTML = 'Инструкции для восстановления пароля отправлены на ваш email.';
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
  setTimeout(function () {
    modal.remove();
  }, 50000);
}
