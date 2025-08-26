<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Allow both POST and PUT
$method = $_SERVER['REQUEST_METHOD'];
$data = [];

if ($method === 'POST') {
    $data = $_POST;
} elseif ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed. Use POST or PUT."]);
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

// Bind all as strings (safe for base64 userId too)
$types = str_repeat('s', count($values));

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Partner requirements updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to update partner requirements: " . $stmt->error
    ]);
} 

$stmt->close();
$conn->close();
?>
