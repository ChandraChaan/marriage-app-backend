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

$sql = "SELECT * FROM candidates ORDER BY submitted_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Applications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            max-width: 95%;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background-color: #3498db;
            color: white;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        th, td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f6fc;
        }

        tbody tr:hover {
            background-color: #eef5ff;
        }

        a {
            color: #2980b9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Submitted Candidate Applications</h2>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Location</th>
                <th>Education</th>
                <th>Year</th>
                <th>Laptop</th>
                <th>Languages</th>
                <th>Role</th>
                <th>Level</th>
                <th>Experience</th>
                <th>Why History</th>
                <th>Availability</th>
                <th>Commitment</th>
                <th>Resume</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row["id"] ?></td>
                <td><?= htmlspecialchars($row["full_name"]) ?></td>
                <td><?= htmlspecialchars($row["phone_number"]) ?></td>
                <td><?= htmlspecialchars($row["email"]) ?></td>
                <td><?= htmlspecialchars($row["location"]) ?></td>
                <td><?= htmlspecialchars($row["education"]) ?></td>
                <td><?= htmlspecialchars($row["education_year"]) ?></td>
                <td><?= htmlspecialchars($row["laptop_availability"]) ?></td>
                <td><?= htmlspecialchars($row["languages"]) ?></td>
                <td><?= htmlspecialchars($row["interested_role"]) ?></td>
                <td><?= htmlspecialchars($row["skill_level"]) ?></td>
                <td><?= htmlspecialchars($row["past_experience"]) ?></td>
                <td><?= htmlspecialchars($row["why_history"]) ?></td>
                <td><?= htmlspecialchars($row["availability"]) ?></td>
                <td><?= htmlspecialchars($row["job_commitment"]) ?></td>
                <td>
                    <?php if (!empty($row["resume_link"])): ?>
                        <a href="<?= htmlspecialchars($row["resume_link"]) ?>" target="_blank">View</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= $row["submitted_at"] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="text-align:center;">No applications submitted yet.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</div>

</body>
</html>