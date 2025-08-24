<?php
// ✅ Show all PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../cors.php';
require '../user_auth.php'; 
require '../db.php';

// Allowed fields from your UserProfile table (exact list you gave)
$allowedFields = [
    'id', 'profile_id', 'name', 'email', 'phone',
    'CreatedAt', 'ProfileCreatedBy', 'MaritalStatus', 'gender', 'Age', 'Height',
    'AnyDisability', 'AboutMyself', 'FatherOccupation', 'MotherOccupation', 
    'FamilyStatus', 'DietFood', 'Religion', 'MotherTongue', 'Community', 'SubCast',
    'CastNoBar', 'Gothram', 'KujaDosham', 'TimeOfBirth', 'CityOfBirth',
    'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',
    'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome',
    'CompanyName', 'Siblings', 'FamilyValues', 'FamilyType', 'LivingWithParents',
    'FamilyIncome', 'dob'
];

// Build SELECT list
$fieldList = implode(', ', $allowedFields);

// Merge GET + POST input
$requestData = array_merge($_GET, $_POST);

// Filters
$filters = [];
$params = [];
$types = "";

// Build filters dynamically
foreach ($allowedFields as $field) {
    if (!empty($requestData[$field])) {
        $filters[] = "$field = ?";
        $params[] = $requestData[$field];

        // Integer vs String
        if (in_array($field, ['id', 'Age', 'Height', 'AnnualIncome', 'Siblings', 'ZipPinCode'])) {
            $types .= "i";
        } else {
            $types .= "s";
        }
    }
}

// Base query
$sql = "SELECT $fieldList FROM UserProfile";

// Add WHERE if filters exist
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare: " . $conn->error, "sql" => $sql]);
    exit;
}

// Bind params
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
        "error" => "No profiles found"
    ]);
}

$stmt->close();
$conn->close();
?>
