<?php
$host = "localhost";
$dbname = "sathya_app_db";
$username = "sathya_bro_user";
$password = "Ch@d1511!S";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed."]));
}
?>