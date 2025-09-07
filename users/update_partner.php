<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../cors.php';
require '../db.php';

// Read input (works for both GET query params or POST/PUT body)
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $_GET;
} else {
    parse_str(file_get_contents("php://input"), $data);
}

// Allowed filter fields (these match your PartnerReqProfile allowedFields)
$allowedFilters = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome',
    'EmploymentType','Education',
];

$whereParts = [];
$values = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFilters, true) && !empty($value)) {
        $whereParts[] = "$key = ?";
        $values[] = $value;
    }
}

$whereClause = "";
if (!empty($whereParts)) {
    $whereClause = "WHERE " . implode(" AND ", $whereParts);
}

// Fetch only useful profile info
$sql = "SELECT id, name, Age, Height, MotherTongue, MaritalStatus, PhysicalStatus,
               Country, State, City, Religion, Cast, SubCast, Dosham,
               EatingHabits, SmokingHabits, DrinkingHabits,
               Qualification, WorkingAs, WorkingWith, ProfessionArea, AnnualIncome,
               EmploymentType, Education
        FROM UserProfile $whereClause";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

// Bind params if filters exist
if (!empty($values)) {
    $types = str_repeat('s', count($values));
    $stmt->bind_param($types, ...$values);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $profiles = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "success" => true,
        "profiles" => $profiles
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to fetch profiles: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
