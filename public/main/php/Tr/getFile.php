<?php
require_once('../config.php');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$student_id = $_POST['student_id'] ?? null;
$file_name = $_POST['file_name'] ?? null;

// You may want to add more security checks here before proceeding

$file_path = "../uploads/" . $file_name;

if (file_exists($file_path)) {
    // Set appropriate headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . filesize($file_path));

    // Read the file and output it to the browser
    readfile($file_path);

    // Send a success message
    echo json_encode(['success' => true, 'message' => 'File found and downloaded successfully']);
} else {
    // Send an error message
    echo json_encode(['success' => false, 'message' => 'File not found']);
}

// Close the database connection
$db->close();
?>
