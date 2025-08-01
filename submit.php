<?php
// MySQL config
$host = "localhost";
$dbname = "sathya_app_db";
$username = "sathya_bro_user";
$password = "Ch@d1511!S";

// Connect
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect data
$full_name = $_POST['full_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];
$location = $_POST['location'];
$education = $_POST['education'];
$education_year = $_POST['education_year'];
$laptop_availability = $_POST['laptop_availability'];
$languages = $_POST['languages'];
$interested_role = $_POST['interested_role'];
$skill_level = $_POST['skill_level'];
$past_experience = $_POST['past_experience'];
$why_history = $_POST['why_history'];
$availability = $_POST['availability'];
$job_commitment = $_POST['job_commitment'];
$resume_link = $_POST['resume_link'];

// Check for duplicates
$check = $conn->prepare("SELECT id FROM candidates WHERE email = ? OR phone_number = ?");
$check->bind_param("ss", $email, $phone_number);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Duplicate found — redirect with error
    header("Location: form.html?status=duplicate");
    exit();
}
$check->close();

// Insert
$sql = "INSERT INTO candidates (
    full_name, phone_number, email, location, education, education_year, laptop_availability,
    languages, interested_role, skill_level, past_experience,
    why_history, availability, job_commitment, resume_link
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssss",
    $full_name, $phone_number, $email, $location, $education, $education_year, $laptop_availability,
    $languages, $interested_role, $skill_level, $past_experience,
    $why_history, $availability, $job_commitment, $resume_link
);

if ($stmt->execute()) {
    // Success — redirect with success
    header("Location: form.html?status=success");
} else {
    // DB error
    header("Location: form.html?status=error");
}

$stmt->close();
$conn->close();
exit();
?>