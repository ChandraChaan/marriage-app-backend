<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Use $_POST directly (since curl --data-urlencode sends form-urlencoded)
$data = $_POST;

// Secure and ordered list of allowed fields to update
$allowedFields = [
    // Account Info
    'name', 'email', 'phone', 'password',

    // Basic Profile
    'ProfileCreatedBy', 'MaritalStatus', 'gender', 'dob', 'Age', 'Height', 'AnyDisability', 'AboutMyself',

    // Family Details
    'FatherOccupation', 'MotherOccupation', 'Siblings', 'FamilyStatus', 'DietFood',

    // Religious Background
    'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',
    'FamilyValues','LivingWithParents','FamilyType','FamilyIncome',

    // Astro Details
    'KujaDosham', 'TimeOfBirth', 'CityOfBirth',

    // Location
    'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',

    // Education & Career
    'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome', 'CompanyName'
];

$setParts = [];
$values = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFields, true)) {
        if ($key === 'password') {
            // Hash password before saving
            // $value = password_hash($value, PASSWORD_BCRYPT);
        }
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

// Determine types: assume all are strings except id at end
$types = str_repeat('s', count($values) - 1) . 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement."]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["message" => "Profile updated successfully."]);
} else {
    echo json_encode(["error" => "Failed to update profile.", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
