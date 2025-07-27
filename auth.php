<?php
require_once 'config.php';

$headers = apache_request_headers();
if (!isset($headers['Authorization']) || $headers['Authorization'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}
?>