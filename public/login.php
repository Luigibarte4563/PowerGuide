<?php
require_once __DIR__ . '/../src/config/env.php';
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

        fetch("save.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            console.log(data); // debug

            if (data.trim() === "success") {
                window.location.href = "dashboard.php";
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