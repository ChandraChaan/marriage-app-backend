<?php
require 'cors.php';
require 'auth.php';
require 'db.php';

echo json_encode(["message" => "Database connection successful."]);
?>