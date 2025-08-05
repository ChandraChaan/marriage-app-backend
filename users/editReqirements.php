<?php
require '../cors.php';
require '../user_auth.php'; // Sets $userId securely via token
require '../db.php';

header('Content-Type: application/json');
 
try {
    // Ordered field list grouped by categories
    $fields = [
        // Basic Details
        'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus', 
        'Country', 'State', 'City', 'Diet',
        
        // Religious Details
        'Religion', 'Cast', 'SubCast', 'Dosham', 
        'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
        
        // Professional Details
        'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
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
        // Organize the data into the requested structure
        $responseData = [
            'basicDetails' => [
                'Profile Created By' => $userData['ProfileCreatedBy'] ?? '',
                'Age' => $userData['Age'] ?? '',
                'Height' => $userData['Height'] ?? '',
                'Mother Tongue' => $userData['MotherTongue'] ?? '',
                'Marital Status' => $userData['MaritalStatus'] ?? '',
                'Country' => $userData['Country'] ?? '',
                'State' => $userData['State'] ?? '',
                'City' => $userData['City'] ?? '',
                'Diet' => $userData['Diet'] ?? '',
            ],
            'religiousDetails' => [
                'Religion' => $userData['Religion'] ?? '',
                'Cast' => $userData['Cast'] ?? '',
                'Sub Cast' => $userData['SubCast'] ?? '',
                'Dosham' => $userData['Dosham'] ?? '',
                'Eating Habits' => $userData['EatingHabits'] ?? '',
                'Smoking Habits' => $userData['SmokingHabits'] ?? '',
                'Drinking Habits' => $userData['DrinkingHabits'] ?? '',
            ],
            'basicDetailsOne' => [
                'Qualification' => $userData['Qualification'] ?? '',
                'Working as' => $userData['WorkingAs'] ?? '',
                'Working With' => $userData['WorkingWith'] ?? '',
                'Profession Area' => $userData['ProfessionArea'] ?? '',
                'Annual Income' => $userData['AnnualIncome'] ?? '',
            ]
        ];

        echo json_encode([
            "success" => true,
            "data" => $responseData
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