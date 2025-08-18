<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId securely via token
require '../db.php';

header('Content-Type: application/json');

try {
    // Ordered and safe field list (excludes password, token, etc.)
    $fields = [ 
        'id', 'CreatedAt',
        'name', 'email', 'phone',
        'ProfileCreatedBy', 'MaritalStatus', 'gender', 'dob', 'Age', 'Height', 'AnyDisability', 'AboutMyself',
        'FatherOccupation', 'MotherOccupation', 'Brother', 'Sister', 'FamilyStatus', 'DietFood',
        'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',
        'KujaDosham', 'TimeOfBirth', 'CityOfBirth',
        'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',
        'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome', 'CompanyName'
    ];

    $selectFields = implode(', ', array_map(fn($f) => "`$f`", $fields));

    // Assume you send gender from frontend securely
    $userGender = $_POST['gender'] ?? null;

    if (!$userGender) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Gender is required."
        ]);
        exit;
    }

    // Find opposite gender
    $oppositeGender = ($userGender === 'Male') ? 'Female' : 'Male';

    // Fetch all profiles of opposite gender (excluding logged-in user)
    $stmt = $conn->prepare("SELECT $selectFields FROM UserProfile WHERE gender = ? AND id != ?");
    $stmt->bind_param("si", $oppositeGender, $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $profiles = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $profiles
    ]);

    $stmt->close(); 
    $conn->close();
} catch (Exception $e) {\

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
}
?> 
