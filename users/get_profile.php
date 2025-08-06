<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId securely via token
require '../db.php';

header('Content-Type: application/json');

try {
    // Ordered and safe field list (excludes password, token, etc.)
    $fields = [
        // System Metadata
        'id', 'CreatedAt',

        // Account Info
        'name', 'email', 'phone',

        // Basic Profile
        'ProfileCreatedBy', 'MaritalStatus', 'gender', 'dob', 'Age', 'Height', 'AnyDisability', 'AboutMyself',

        // Family Details
        'FatherOccupation', 'MotherOccupation', 'Brother', 'Sister', 'FamilyStatus', 'DietFood',

        // Religious Background
        'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',

        // Astro Details
        'KujaDosham', 'TimeOfBirth', 'CityOfBirth',

        // Location
        'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',

        // Education & Career
        'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome', 'CompanyName',
        
        // Profile Image (stores filename only)
        'ProfileImage'
    ];

    // Prepare dynamic field list for query
    $selectFields = implode(', ', array_map(fn($f) => "`$f`", $fields));

    $stmt = $conn->prepare("SELECT $selectFields FROM UserProfile WHERE id = ?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed");
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData) {
        // Return the image filename as stored in DB
        // Frontend will need to construct the full URL to display it
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