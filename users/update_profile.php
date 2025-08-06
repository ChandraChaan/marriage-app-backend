<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

// Initialize response array
$response = [];

// Secure and ordered list of allowed fields to update
$allowedFields = [
    // Account Info
    'name', 'email', 'phone', 'password',

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

    // Image field
    'ProfileImage'
];

$setParts = [];
$values = [];

// Handle file upload if present
if (isset($_FILES['ProfileImage']) && $_FILES['ProfileImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/profile_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($_FILES['ProfileImage']['name']);
    $targetPath = $uploadDir . $fileName;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['ProfileImage']['type'];
    
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['ProfileImage']['tmp_name'], $targetPath)) {
            $setParts[] = "ProfileImage = ?";
            $values[] = $fileName;
        } else {
            $response['error'] = "Failed to upload image.";
        }
    } else {
        $response['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
    }
}

// Handle other POST data
parse_str(file_get_contents("php://input"), $data);

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFields)) {
        if ($key === 'password') {
            // Optional: hash the password before saving
            // $value = password_hash($value, PASSWORD_BCRYPT);
        }
        $setParts[] = "$key = ?";
        $values[] = $value;
    }
}

if (empty($setParts) {
    $response['error'] = "No valid fields provided.";
    echo json_encode($response);
    exit;
}

$setClause = implode(", ", $setParts);
$sql = "UPDATE UserProfile SET $setClause WHERE id = ?";
$values[] = $userId;

// Determine types: assume all are strings except id at end
$types = str_repeat('s', count($values) - 1) . 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response['error'] = "Failed to prepare statement.";
    echo json_encode($response);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    $response['message'] = "Profile updated successfully.";
    if (isset($fileName)) {
        $response['imagePath'] = $fileName;
    }
} else {
    $response['error'] = "Failed to update profile.";
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>