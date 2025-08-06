<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Secure list of fields to retrieve for Partner Requirements
// Added 'userId' as the first field
$allowedFields = [
    'userId', // Added as first field
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
];

// Convert array to comma-separated string for SQL query
$fieldList = implode(', ', $allowedFields);

$sql = "SELECT $fieldList FROM PartnerReqProfile WHERE userId = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $partnerRequirements = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "data" => $partnerRequirements
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "No partner requirements found for this user"
    ]);
}

$stmt->close();
$conn->close();
?>