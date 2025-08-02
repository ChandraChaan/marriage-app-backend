<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId securely via token
require '../db.php';

header('Content-Type: application/json');

try {
    // Define only safe, required fields (to avoid leaking passwords, tokens, etc.)
    $fields = [
        'id', 'name', 'email', 'phone', 'gender', 'dob',
        'ProfileCreatedBy', 'MaritalStatus', 'Height', 'Age', 'AnyDisability',
        'FatherOccupation', 'MotherOccupation', 'Brother', 'Sister', 'FamilyStatus', 'DietFood',
        'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',
        'KujaDosham', 'TimeOfBirth', 'CityOfBirth',
        'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',
        'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome', 'CompanyName',
        'AboutMyself', 'CreatedAt'
    ];

    $selectFields = implode(', ', array_map(fn($f) => "`$f`", $fields));

    $stmt = $conn->prepare("SELECT $selectFields FROM UserProfile WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData) {
        echo json_encode([
            "success" => true,
            "data" => $userData
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "User profile not found."
        ]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
}
?>