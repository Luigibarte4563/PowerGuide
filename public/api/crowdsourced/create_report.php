<?php

session_start();

header("Content-Type: application/json");

include "../../src/config/db_connect.php";

$conn = getConnection();

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}

$user_id = $_SESSION["user_id"];

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$stmt = $conn->prepare("
    INSERT INTO outage_reports
    (
        user_id,
        location_name,
        latitude,
        longitude,
        category,
        severity,
        description
    )
    VALUES
    (
        :user_id,
        :location_name,
        :latitude,
        :longitude,
        :category,
        :severity,
        :description
    )
");

$stmt->execute([

    ":user_id" => $user_id,

    ":location_name" =>
        $data["location_name"],

    ":latitude" =>
        $data["latitude"],

    ":longitude" =>
        $data["longitude"],

    ":category" =>
        $data["category"],

    ":severity" =>
        $data["severity"],

    ":description" =>
        $data["description"]
]);

echo json_encode([
    "success" => true,
    "message" => "Report submitted successfully"
]);