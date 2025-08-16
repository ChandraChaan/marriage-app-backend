<?php
require '../cors.php';
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = $data['password'];

$stmt = $conn->prepare("SELECT * FROM UserProfile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $token = base64_encode($user['id'] . "|" . time());
        echo json_encode([
            "message" => "Login successful.",
            "token" => $token,
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ]);
    } else {
        echo json_encode(["error" => "Invalid password."]);
    }
} else {
    echo json_encode(["error" => "User not found."]);
}
?> 