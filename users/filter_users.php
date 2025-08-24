<?php
// ✅ Show all errors while debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available if needed
require '../db.php';

// Allowed fields from PartnerReqProfile
$allowedFields = [
    'p.userId',
    'p.ProfileCreatedBy', 'p.Age', 'p.Height', 'p.MotherTongue', 'p.MaritalStatus',
    'p.PhysicalStatus', 'p.Country', 'p.State', 'p.City',
    'p.Religion', 'p.Cast', 'p.SubCast', 'p.Dosham',
    'p.EatingHabits', 'p.SmokingHabits', 'p.DrinkingHabits',
    'p.Qualification', 'p.WorkingAs', 'p.WorkingWith', 'p.ProfessionArea', 'p.AnnualIncome',
    'u.Gender', 'u.token'
];

// Build SELECT list
$fieldList = implode(', ', $allowedFields);

// ✅ Merge GET and POST for filters
$requestData = array_merge($_GET, $_POST);

$filters = [];
$params = [];
$types = "";

// Allowed filter fields (without table alias, user can pass Gender=Female etc.)
$filterableFields = [
    'userId' => 'p.userId',
    'ProfileCreatedBy' => 'p.ProfileCreatedBy',
    'Age' => 'p.Age',
    'Height' => 'p.Height',
    'MotherTongue' => 'p.MotherTongue',
    'MaritalStatus' => 'p.MaritalStatus',
    'PhysicalStatus' => 'p.PhysicalStatus',
    'Country' => 'p.Country',
    'State' => 'p.State',
    'City' => 'p.City',
    'Religion' => 'p.Religion',
    'Cast' => 'p.Cast',
    'SubCast' => 'p.SubCast',
    'Dosham' => 'p.Dosham',
    'EatingHabits' => 'p.EatingHabits',
    'SmokingHabits' => 'p.SmokingHabits',
    'DrinkingHabits' => 'p.DrinkingHabits',
    'Qualification' => 'p.Qualification',
    'WorkingAs' => 'p.WorkingAs',
    'WorkingWith' => 'p.WorkingWith',
    'ProfessionArea' => 'p.ProfessionArea',
    'AnnualIncome' => 'p.AnnualIncome',
    'Gender' => 'u.Gender',
    'token' => 'u.token'
];

// Build filters dynamically
foreach ($filterableFields as $inputField => $dbField) {
    if (isset($requestData[$inputField]) && $requestData[$inputField] !== '') {
        $filters[] = "$dbField = ?";
        $params[] = $requestData[$inputField];

        // Type binding
        if (in_array($inputField, ['userId', 'Age', 'Height', 'AnnualIncome'])) {
            $types .= "i"; // integer
        } else {
            $types .= "s"; // string
        }
    }
}

// Base query with JOIN
$sql = "SELECT $fieldList 
        FROM PartnerReqProfile p
        JOIN UserProfile u ON p.userId = u.userId";

// Add WHERE if needed
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare: " . $conn->error]);
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
