<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

parse_str(file_get_contents("php://input"), $data);

// Allowed fields in the database
$allowedFields = [
    'ProfileCreatedBy', 'MaritalStatus', 'Height', 'Age', 'gender', 'AnyDisability',
    'FatherOccupation', 'MotherOccupation', 'Brother', 'Sister', 'FamilyStatus', 'DietFood',
    'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',
    'KujaDosham', 'dob', 'TimeOfBirth', 'CityOfBirth',
    'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',
    'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome', 'CompanyName',
    'AboutMyself', 'name', 'email', 'phone', 'password'
];

// Build dynamic query
$setParts = [];
$values = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFields)) {
        $setParts[] = "$key = ?";
        $values[] = $value;
    }
}

if (empty($setParts)) {
    echo json_encode(["error" => "No valid fields provided."]);
    exit;
}

$setClause = implode(", ", $setParts);
$sql = "UPDATE UserProfile SET $setClause WHERE id = ?";
$values[] = $userId;

$stmt = $conn->prepare($sql);
$types = str_repeat('s', count($values) - 1) . 'i';
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["message" => "Profile updated successfully."]);
} else {
    echo json_encode(["error" => "Failed to update profile."]);
}
?>