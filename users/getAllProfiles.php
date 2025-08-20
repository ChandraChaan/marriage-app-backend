<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId securely via token
require '../db.php';

header('Content-Type: application/json');

try {
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

    // Get logged-in user's gender
    $stmt = $conn->prepare("SELECT gender FROM UserProfile WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !isset($user['gender'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "User not found or gender missing."
        ]);
        exit;
    }

    $userGender = $user['gender'];
    $oppositeGender = (strtolower(trim($userGender)) === 'male') ? 'Female' : 'Male';

    // Fetch opposite gender profiles
    $stmt = $conn->prepare("SELECT $selectFields FROM UserProfile WHERE LOWER(TRIM(gender)) = LOWER(TRIM(?)) AND id != ?");
    $stmt->bind_param("si", $oppositeGender, $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $profiles = $result->fetch_all(MYSQLI_ASSOC);
    
    // Add unique profile_id (not same as id or userId)
    $profilesWithProfileId = array_map(function($profile) {
        $profile['profile_id'] = "P" . $profile['id']; // Make it unique
        return $profile;
    }, $profiles);

    echo json_encode([
        "success" => true,
        "user_id" => $userId, // keep logged in userId in response
        "data" => $profilesWithProfileId
    ]);

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
