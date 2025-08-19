<?php
require '../cors.php';
require '../db.php'; // removed user_auth.php since token not required

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

    // ✅ Get gender from request (mandatory)
    $input = json_decode(file_get_contents("php://input"), true);
    $gender = $input['gender'] ?? null;

    if (!$gender) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Gender is required."
        ]);
        exit;
    }

    // Determine opposite gender safely (case-insensitive)
    $oppositeGender = (strtolower(trim($gender)) === 'male') ? 'Female' : 'Male';

    // Fetch all profiles of opposite gender
    $stmt = $conn->prepare("SELECT $selectFields FROM UserProfile WHERE LOWER(TRIM(gender)) = LOWER(TRIM(?))");
    $stmt->bind_param("s", $oppositeGender);
    $stmt->execute();

    $result = $stmt->get_result();
    $profiles = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $profiles
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
