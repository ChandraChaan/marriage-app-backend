<?php
require '../cors.php';
require '../user_auth.php'; // Still need authentication for security
require '../db.php';

// Secure list of fields to retrieve for Partner Requirements
$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
];

// Convert array to comma-separated string for SQL query
$fieldList = implode(', ', $allowedFields);

// Get the latest partner requirements (not tied to any user)
$sql = "SELECT $fieldList FROM PartnerReqProfile ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

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
        "error" => "No partner requirements found"
    ]);
}

$stmt->close();
$conn->close();
?>