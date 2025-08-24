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

    // Base query
    $query = "SELECT $selectFields FROM UserProfile WHERE LOWER(TRIM(gender)) = LOWER(TRIM(?)) AND id != ?";
    $params = [$oppositeGender, $userId];
    $types = "si";

    // Filterable fields
    $filterableFields = [
        'ProfileCreatedBy', 'MaritalStatus', 'Age', 'Height', 'AnyDisability',
        'FatherOccupation', 'MotherOccupation', 'Siblings', 'FamilyStatus', 'DietFood',
        'Religion', 'MotherTongue', 'Community', 'SubCast', 'CastNoBar', 'Gothram',
        'KujaDosham', 'CityOfBirth', 'FamilyValues', 'LivingWithParents', 'FamilyType',
        'FamilyIncome', 'State', 'CountryLiving', 'City', 'ResidencyStat',
        'Qualification', 'WorkingAs', 'AnnualIncome'
    ];

    // Check if any filter parameters are provided
    $filterConditions = [];
    foreach ($filterableFields as $field) {
        if (isset($_GET[$field]) && !empty(trim($_GET[$field]))) {
            $filterValue = trim($_GET[$field]);
            
            // For numeric fields, use exact match
            if (in_array($field, ['Age', 'Height', 'Siblings', 'FamilyIncome', 'AnnualIncome'])) {
                $filterConditions[] = "`$field` = ?";
                $params[] = $filterValue;
                $types .= "i";
            } 
            // For text fields, use LIKE for partial matching
            else {
                $filterConditions[] = "`$field` LIKE ?";
                $params[] = "%$filterValue%";
                $types .= "s";
            }
        }
    }

    // Add filter conditions to query
    if (!empty($filterConditions)) {
        $query .= " AND (" . implode(" AND ", $filterConditions) . ")";
    }

    // Check if search query is provided
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $searchTerm = trim($_GET['search']);
        $searchConditions = [];
        
        // Create search conditions for each field
        foreach ($fields as $field) {
            // Skip fields that shouldn't be searched
            if (in_array($field, ['id', 'CreatedAt'])) continue;
            
            $searchConditions[] = "`$field` LIKE ?";
            $params[] = "%$searchTerm%";
            $types .= "s";
        }
        
        if (!empty($searchConditions)) {
            $query .= " AND (" . implode(" OR ", $searchConditions) . ")";
        }
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    // Bind parameters dynamically
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