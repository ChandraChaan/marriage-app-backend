<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId after verifying auth token
require '../db.php';

// Set response content type
header('Content-Type: application/json');

try {
    // Prepare and bind
    $stmt = $conn->prepare("
        SELECT 
            id AS user_id, 
            name AS full_name, 
            email, 
            phone, 
            gender, 
            dob 
        FROM UserProfile 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and respond
    if ($userData = $result->fetch_assoc()) {
        http_response_code(200); // OK
        echo json_encode([
            "success" => true,
            "data" => $userData
        ]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode([
            "success" => false,
            "error" => "User profile not found."
        ]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500); // Server Error
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
}
?>
