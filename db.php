<?php
// db.php
$DB_HOST = 'localhost';
$DB_USER = 'u930527350_property_form_';
$DB_PASS = 'Kanha5140@qqq';
$DB_NAME = 'u930527350_property_form_';


$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
error_log("DB Connection failed: " . $mysqli->connect_error);
http_response_code(500);
echo json_encode(["success" => false, "message" => "Database connection failed."]);
exit;
}
$mysqli->set_charset('utf8mb4');