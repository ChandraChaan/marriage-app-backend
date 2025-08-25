<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Ensure request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed. Use PUT."]);
    exit;
}

// Get PUT data (usually sent as JSON)
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Fallback: if JSON decoding fails, try parsing as form-urlencoded
if (!is_array($data)) {
    parse_str($input, $data);
}

if (!is_array($data)) {
    echo json_encode(["success" => false, "error" => "Invalid input format."]);
    exit;
}

// Secure and ordered list of allowed fields to update for Partner Requirements
$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome',
    'EmploymentType','Education',
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

$setClause = implode(", ", $setParts);
$sql = "UPDATE PartnerReqProfile SET $setClause WHERE userId = ?";
$values[] = $userId;

// Determine parameter types: all strings except the final userId which is an int
$types = str_repeat('s', count($values) - 1) . 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        "success" => true,
        "message" => "Partner requirements updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "No rows updated (check userId or values)."
    ]);
}

$stmt->close();
$conn->close();
?>
