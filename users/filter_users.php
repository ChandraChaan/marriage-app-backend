<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Allowed fields (columns that can be searched or retrieved)
$allowedFields = [
    'userId',
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome',
    'Gender', 'token' // added
];

// Build SELECT list
$fieldList = implode(', ', $allowedFields);

// ✅ Collect filters from GET or POST request
$filters = [];
$params = [];
$types = "";

// Loop through allowed fields and check if user provided a value to filter by
foreach ($allowedFields as $field) {
    if (isset($_GET[$field]) && $_GET[$field] !== '') {
        $filters[] = "$field = ?";
        $params[] = $_GET[$field];
        $types .= "s"; // assume string, adjust if you know type (e.g. 'i' for int)
    }
}

// Base query
$sql = "SELECT $fieldList FROM PartnerReqProfile";

// Add WHERE clause only if filters exist
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

// Bind params dynamically
if (!empty($filters)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
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
