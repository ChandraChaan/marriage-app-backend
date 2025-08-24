<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Allowed fields
$allowedFields = [
    'userId',
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome',
    'Gender', 'token'
];

// Build SELECT list
$fieldList = implode(', ', $allowedFields);

// ✅ Merge GET and POST for flexibility
$requestData = array_merge($_GET, $_POST);

$filters = [];
$params = [];
$types = "";

// Loop through allowed fields and check if request has that value
foreach ($allowedFields as $field) {
    if (isset($requestData[$field]) && $requestData[$field] !== '') {
        $filters[] = "$field = ?";
        $params[] = $requestData[$field];
        $types .= "s"; // default string (you can change to 'i' if numeric like Age, userId, etc.)
    }
}

$sql = "SELECT $fieldList FROM PartnerReqProfile";

if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

if (!empty($filters)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $profiles = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode([
        "success" => true,
        "count" => count($profiles),
        "data" => $profiles
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "No profiles found matching criteria"
    ]);
}

$stmt->close();
$conn->close();
?>
