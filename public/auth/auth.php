<?php
session_start();

$page = $_GET['page'] ?? 'login'; // login or register
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth System</title>
</head>
<body>

<div class="box">

<?php if ($page === 'login'): ?>

    <!-- LOGIN FORM -->
    <h2>Login</h2>

    <form action="../api/auth/jwt_login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>

        <button type="submit">Login</button>
    </form>

    <a href="auth.php?page=register">No account? Register here</a>

<?php else: ?>

    <!-- REGISTER FORM -->
    <h2>Register</h2>

<form action="../api/auth/register.php" method="POST" id="registerForm">

    <input type="text" name="name" placeholder="Full Name" required><br><br>

    <input type="email" name="email" placeholder="Email" required><br><br>

    <!-- PASSWORD -->
    <div>
        <input type="password"
               id="password"
               name="password"
               placeholder="Password"
               required>

        <button type="button" onclick="togglePassword('password')">👁</button>
    </div>

    <div id="password-message"></div><br>

    <!-- CONFIRM PASSWORD -->
    <div>
        <input type="password"
               id="confirm_password"
               name="confirm_password"
               placeholder="Confirm Password"
               required>

        <button type="button" onclick="togglePassword('confirm_password')">👁</button>
    </div>

    <div id="match-message"></div><br>

    <button type="submit" id="submitBtn">Register</button>

</form>
<script>

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const message = document.getElementById("password-message");
const matchMessage = document.getElementById("match-message");
const submitBtn = document.getElementById("submitBtn");

/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD
|--------------------------------------------------------------------------
*/
function togglePassword(id) {

    const field = document.getElementById(id);

    field.type = field.type === "password" ? "text" : "password";
}

/*
|--------------------------------------------------------------------------
| PASSWORD STRENGTH (SOFT - DOES NOT BLOCK)
|--------------------------------------------------------------------------
*/
password.addEventListener("input", function () {

    let value = password.value;
    let strength = 0;

    if (value.length >= 6) strength++;
    if (/[A-Z]/.test(value)) strength++;
    if (/[a-z]/.test(value)) strength++;
    if (/\d/.test(value)) strength++;

    if (value.length === 0) {
        message.innerHTML = "";
    }
    else if (strength <= 2) {
        message.innerHTML = "Weak password (you can still continue)";
        message.style.color = "red";
    }
    else if (strength === 3) {
        message.innerHTML = "Medium password";
        message.style.color = "orange";
    }
    else {
        message.innerHTML = "Strong password";
        message.style.color = "green";
    }

    checkMatch();
});

/*
|--------------------------------------------------------------------------
| PASSWORD MATCH CHECK (ONLY BLOCK IF MISMATCH)
|--------------------------------------------------------------------------
*/
confirmPassword.addEventListener("input", checkMatch);

function checkMatch() {

    if (confirmPassword.value.length === 0) {
        matchMessage.innerHTML = "";
        submitBtn.disabled = false;
        return;
    }

    if (password.value === confirmPassword.value) {
        matchMessage.innerHTML = "Passwords match";
        matchMessage.style.color = "green";
        submitBtn.disabled = false;
    } else {
        matchMessage.innerHTML = "Passwords do not match";
        matchMessage.style.color = "red";
        submitBtn.disabled = true;
    }
}

/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD BUTTON
|--------------------------------------------------------------------------
*/
function togglePassword(id) {

    const field = document.getElementById(id);

    field.type = field.type === "password" ? "text" : "password";
}

</script>

<a href="auth.php?page=login">Already have an account? Login</a>

<?php endif; ?>

</div><br>

<?php
require_once __DIR__ . '/../../src/config/env.php';
$googleClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script src="https://accounts.google.com/gsi/client" async></script>

    <script>
      function decodeJWT(token) {

        let base64Url = token.split(".")[1];
        let base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        let jsonPayload = decodeURIComponent(
          atob(base64)
            .split("")
            .map(function (c) {
              return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
        );
        return JSON.parse(jsonPayload);
      }

      function handleCredentialResponse(response) {

        const responsePayload = decodeJWT(response.credential);

        const formData = new FormData();
        formData.append("name", responsePayload.name);
        formData.append("email", responsePayload.email);
        formData.append("picture", responsePayload.picture);
        formData.append("sub", responsePayload.sub);

        fetch("../api/auth/google_auth.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            console.log(data); // debug

            if (data.trim() === "success") {
                window.location.href = "../dashboard/user/user.php";
            } else {
                alert("Error: " + data);
            }
        })
        .catch(err => console.error(err));

        // console.log("Decoded JWT ID token fields:");
        // console.log("  Full Name: " + responsePayload.name);
        // console.log("  Given Name: " + responsePayload.given_name);
        // console.log("  Family Name: " + responsePayload.family_name);
        // console.log("  Unique ID: " + responsePayload.sub);
        // console.log("  Profile image URL: " + responsePayload.picture);
        // console.log("  Email: " + responsePayload.email);
      }
    </script>
  </head>
  <body>
    <!-- g_id_onload contains Google Identity Services settings -->
    <div
      id="g_id_onload"
      data-auto_prompt="false"
      data-callback="handleCredentialResponse"
      data-client_id="<?= $googleClientId ?>" 
    ></div>
    <!-- g_id_signin places the button on a page and supports customization -->
    <div class="g_id_signin"></div>
  </body>
</html>


</body>
</html>