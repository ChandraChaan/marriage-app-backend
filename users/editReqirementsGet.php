<?php
require '../cors.php';
require '../user_auth.php'; // This must set $userId
require '../db.php';

// Ensure userId is set and valid
if (!isset($userId) || empty($userId)) {
    echo json_encode(["error" => "Invalid user authentication."]);
    exit;
}

// Prepare SQL to fetch user-specific partner requirement profile
$sql = "SELECT 
    ProfileCreatedBy, Age, Height, MotherTongue, MaritalStatus,
    PhysicalStatus, Country, State, City,
    Religion, Cast, SubCast, Dosham,
    EatingHabits, SmokingHabits, DrinkingHabits,
    Qualification, WorkingAs, WorkingWith, ProfessionArea, AnnualIncome
    FROM PartnerReqProfile WHERE userId = ? LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["message" => "No partner requirement profile found."]);
} else {
    $profile = $result->fetch_assoc();
    echo json_encode(["userId" => $userId, "profile" => $profile]);
}

// Clean up
$stmt->close();
$conn->close();
?>
