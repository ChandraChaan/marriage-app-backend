<?php
require '../cors.php';
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];

$stmt = $conn->prepare("SELECT id FROM UserProfile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

echo $result->num_rows > 0
    ? json_encode(["message" => "Reset link sent (mock)."])
    : json_encode(["error" => "Email not found."]);
?>