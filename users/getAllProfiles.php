<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId securely via token
require '../db.php';

header('Content-Type: application/json');

try {
    // First, get the latest partner requirements (without userId dependency)
    $partnerReqSql = "SELECT * FROM PartnerReqProfile ORDER BY id DESC LIMIT 1";
    $partnerReqStmt = $conn->prepare($partnerReqSql);
    $partnerReqStmt->execute();
    $partnerReqResult = $partnerReqStmt->get_result();
    $partnerRequirements = $partnerReqResult->fetch_assoc();
    $partnerReqStmt->close();

    // If no partner requirements found, return all opposite gender profiles
    $hasPartnerRequirements = $partnerRequirements && count(array_filter($partnerRequirements)) > 0;

    // Ordered and safe field list (excludes password, token, etc.)
    $fields = [ 
        'id', 'CreatedAt',
        'name', 'email', 'phone',
        'ProfileCreatedBy', 'MaritalStatus', 'gender', 'dob', 'Age', 'Height', 'AnyDisability', 'AboutMyself',
        'FatherOccupation', 'MotherOccupation', 'Siblings', 'FamilyStatus', 'DietFood',
        'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',
        'KujaDosham', 'TimeOfBirth', 'CityOfBirth',
        'FamilyValues', 'LivingWithParents', 'FamilyType', 'FamilyIncome',
        'State', 'CountryLiving', 'City', 'ResidencyStat', 'ZipPinCode',
        'Qualification', 'College', 'WorkingCompany', 'WorkingAs', 'AnnualIncome', 'CompanyName'
    ];

    // PHP < 7.4 compatible
    $selectFields = implode(', ', array_map(function ($f) { return "`$f`"; }, $fields));

    // Get logged-in user's gender from database
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

    // Determine opposite gender safely (case-insensitive)
    $oppositeGender = (strtolower(trim($userGender)) === 'male') ? 'Female' : 'Male';

    // Base query for opposite gender profiles
    $baseQuery = "SELECT $selectFields FROM UserProfile WHERE LOWER(TRIM(gender)) = LOWER(TRIM(?)) AND id != ?";
    $params = [$oppositeGender, $userId];
    $types = "si";

    // If partner requirements exist, add matching conditions
    if ($hasPartnerRequirements) {
        $conditions = [];
        
        // Map partner requirement fields to user profile fields
        $fieldMappings = [
            'ProfileCreatedBy' => 'ProfileCreatedBy',
            'Age' => 'Age',
            'Height' => 'Height',
            'MotherTongue' => 'MotherTongue',
            'MaritalStatus' => 'MaritalStatus',
            'PhysicalStatus' => 'AnyDisability',
            'Country' => 'CountryLiving',
            'State' => 'State',
            'City' => 'City',
            'Religion' => 'Religion',
            'Cast' => 'Community',
            'SubCast' => 'SubCast',
            'Dosham' => 'KujaDosham',
            'EatingHabits' => 'DietFood',
            'Qualification' => 'Qualification',
            'WorkingAs' => 'WorkingAs',
            'AnnualIncome' => 'AnnualIncome'
        ];
        
        foreach ($fieldMappings as $reqField => $profileField) {
            if (!empty($partnerRequirements[$reqField])) {
                $conditions[] = "`$profileField` = ?";
                $params[] = $partnerRequirements[$reqField];
                $types .= "s";
            }
        }
        
        // If we have any conditions, add them to the query with OR logic
        if (!empty($conditions)) {
            $baseQuery .= " AND (" . implode(" OR ", $conditions) . ")";
        }
    }

    // Fetch profiles with potential partner requirement filtering
    $stmt = $conn->prepare($baseQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    $profiles = $result->fetch_all(MYSQLI_ASSOC);
    
    // Add profile_id field to each profile (using the existing 'id' field)
    $profilesWithProfileId = array_map(function($profile) {
        $profile['profile_id'] = $profile['id']; // Add profile_id field
        return $profile;
    }, $profiles);

    echo json_encode([
        "success" => true,
        "hasPartnerRequirements" => $hasPartnerRequirements,
        "data" => $profilesWithProfileId // Return profiles with profile_id
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