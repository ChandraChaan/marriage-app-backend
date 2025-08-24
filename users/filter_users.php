<?php
// ✅ Show all errors (for debugging only, remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available if needed
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

// ✅ Merge GET and POST data (so you can send either way)
$requestData = array_merge($_GET, $_POST);

// Build filters
$filters = [];
$params = [];
$types = "";

// Choose correct param types (i = integer, s = string)
foreach ($allowedFields as $field) {
    if (isset($requestData[$field]) && $requestData[$field] !== '') {
        $filters[] = "$field = ?";
        $params[] = $requestData[$field];

        if (in_array($field, ['userId', 'Age', 'Height', 'AnnualIncome'])) {
            $types .= "i"; // integer fields
        } else {
            $types .= "s"; // string fields
        }
    }
}

// Base query
$sql = "SELECT $fieldList FROM PartnerReqProfile";

// Add WHERE if needed
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

// Debug (optional): uncomment to see query + params
// echo "SQL: " . $sql . "\n";
// echo "Params: " . json_encode($params) . "\n";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare: " . $conn->error]);
    exit;
}

// Bind parameters if filters exist
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
