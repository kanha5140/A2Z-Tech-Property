<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php'; // this defines $mysqli

function clean($s) {
  return trim($s);
}

// Helper to handle uploads
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$allowedMime = ['image/jpeg','image/png','application/pdf','image/gif'];
$uploadedNames = [];

// Basic server-side validation (required fields)
$full_name = clean($_POST['full_name'] ?? '');
$mobile = clean($_POST['mobile'] ?? '');
if (!$full_name || !$mobile) {
  echo json_encode(['success'=>false,'message'=>'Full name and mobile are required']); exit;
}

// Collect other fields safely
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$address = $mysqli->real_escape_string($_POST['address'] ?? '');
$city = $mysqli->real_escape_string($_POST['city'] ?? '');
$services = $mysqli->real_escape_string($_POST['services'] ?? '');
$property_type = $mysqli->real_escape_string($_POST['property_type'] ?? '');
$site_address = $mysqli->real_escape_string($_POST['site_address'] ?? '');
$site_size = $mysqli->real_escape_string($_POST['site_size'] ?? '');
$budget = $mysqli->real_escape_string($_POST['budget'] ?? '');
$timeline = $mysqli->real_escape_string($_POST['timeline'] ?? '');
$service_mode = $mysqli->real_escape_string($_POST['service_mode'] ?? '');
$heard_about = $mysqli->real_escape_string($_POST['heard_about'] ?? '');
$additional_requirements = $mysqli->real_escape_string($_POST['additional_requirements'] ?? '');
$whatsapp = $mysqli->real_escape_string($_POST['whatsapp'] ?? '');

// Handle file uploads (multiple)
if (!empty($_FILES['files'])) {
  foreach ($_FILES['files']['error'] as $i => $err) {
    if ($err !== UPLOAD_ERR_OK) continue;
    $tmp = $_FILES['files']['tmp_name'][$i];
    $name = basename($_FILES['files']['name'][$i]);
    $mime = mime_content_type($tmp);
    if (!in_array($mime, $allowedMime)) continue; // skip disallowed types
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (move_uploaded_file($tmp, $uploadDir . '/' . $newName)) {
      $uploadedNames[] = $mysqli->real_escape_string($newName);
    }
  }
}

$uploaded_csv = implode(',', $uploadedNames);

// Prepared statement to insert record
$stmt = $mysqli->prepare("INSERT INTO property_requests
  (full_name,mobile,whatsapp,email,address,city,services,property_type,site_address,site_size,budget,timeline,uploaded_files,service_mode,heard_about,additional_requirements)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

if (!$stmt) {
  echo json_encode(['success'=>false,'message'=>'Prepare failed']); exit;
}

$stmt->bind_param('ssssssssssssssss',
  $full_name, $mobile, $whatsapp, $email, $address, $city, $services, $property_type, $site_address, $site_size, $budget, $timeline, $uploaded_csv, $service_mode, $heard_about, $additional_requirements
);

$ok = $stmt->execute();
if (!$ok) {
  error_log('Insert error: ' . $stmt->error);
  echo json_encode(['success'=>false,'message'=>'Database write failed']);
  exit;
}

echo json_encode(['success'=>true,'message'=>'Saved']);