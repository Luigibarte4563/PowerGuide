<?php
session_start();

require_once __DIR__ . '../../src/config/env.php';
$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", "$user", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (
        isset($_POST['name']) &&
        isset($_POST['email']) &&
        isset($_POST['picture']) &&
        isset($_POST['sub'])
    ) {

        $name = $_POST['name'];
        $email = $_POST['email'];
        $picture = $_POST['picture'];
        $google_id = $_POST['sub'];

        // Check if user exists
        $sql = "SELECT * FROM users WHERE google_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$google_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Update user info
            $update = "UPDATE users SET name=?, email=?, picture=? WHERE google_id=?";
            $stmt = $conn->prepare($update);
            $stmt->execute([$name, $email, $picture, $google_id]);
        } else {
            // Insert new user
            $insert = "INSERT INTO users (google_id, name, email, picture) VALUES (?,?,?,?)";
            $stmt = $conn->prepare($insert);
            $stmt->execute([$google_id, $name, $email, $picture]);
        }

        // Save session
        $_SESSION['user'] = [
            "name" => $name,
            "email" => $email,
            "picture" => $picture,
            "google_id" => $google_id
        ];

        echo "success";

    } else {
        echo "Missing POST data";
    }

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
?>