<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId after verifying auth token
require '../db.php';

header('Content-Type: application/json');

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    } 

    // Explicitly select only the fields you want for clarity
    $query = "SELECT id, image_url, description, website_url FROM cards";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all rows as associative arrays
    $cards = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "success" => true,
        "count" => count($cards),
        "data" => $cards
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error",
        "message" => $e->getMessage()
    ]);
}
