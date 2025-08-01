<?php
// MySQL config
$host = "localhost";
$dbname = "sathya_app_db";
$username = "sathya_bro_user";
$password = "Ch@d1511!S";

// Create DB connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize and collect POST data
$full_name = $_POST['full_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];
$location = $_POST['location'];
$education = $_POST['education'];
$laptop_availability = $_POST['laptop_availability'];
$languages = $_POST['languages'];
$interested_role = $_POST['interested_role'];
$skill_level = $_POST['skill_level'];
$past_experience = $_POST['past_experience'];
$why_history = $_POST['why_history'];
$how_heard = $_POST['how_heard'];
$availability = $_POST['availability'];
$job_commitment = $_POST['job_commitment'];
$resume_link = $_POST['resume_link'];

// Insert into DB
$sql = "INSERT INTO candidates (
    full_name, phone_number, email, location, education, laptop_availability,
    languages, interested_role, skill_level, past_experience,
    why_history, how_heard, availability, job_commitment, resume_link
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssss",
    $full_name, $phone_number, $email, $location, $education, $laptop_availability,
    $languages, $interested_role, $skill_level, $past_experience,
    $why_history, $how_heard, $availability, $job_commitment, $resume_link
);

if ($stmt->execute()) {
    echo "Application submitted successfully. Thank you!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>