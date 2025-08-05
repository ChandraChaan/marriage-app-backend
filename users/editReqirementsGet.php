<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

parse_str(file_get_contents("php://input"), $data);

// Secure and ordered list of allowed fields to update for Partner Requirements
$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
];

$setParts = [];
$values = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFields)) {
        $setParts[] = "$key = ?";
        $values[] = $value;
    }
}

if (empty($setParts)) {
    echo json_encode(["success" => false, "error" => "No valid fields provided for update."]);
    exit;
}

// Check if profile exists
$checkSql = "SELECT userId FROM PartnerReqProfile WHERE userId = ? LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$profileExists = ($checkResult->num_rows > 0);
$checkStmt->close();

if ($profileExists) {
    // Update existing profile
    $setClause = implode(", ", $setParts);
    $sql = "UPDATE PartnerReqProfile SET $setClause WHERE userId = ?";
    $values[] = $userId;
    
    // Determine types: assume all are strings except userId at end
    $types = str_repeat('s', count($values) - 1) . 'i';
} else {
    // Insert new profile
    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($values), '?'));
    $sql = "INSERT INTO PartnerReqProfile ($columns, userId) VALUES ($placeholders, ?)";
    $values[] = $userId;
    
    // All parameters are strings except userId at end
    $types = str_repeat('s', count($values) - 1) . 'i';
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Partner requirements " . ($profileExists ? "updated" : "created") . " successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to " . ($profileExists ? "update" : "create") . " partner requirements: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>