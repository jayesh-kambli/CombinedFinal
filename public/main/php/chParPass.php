<?php
require_once ('config.php');

// Establish database connection
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($db->connect_error) {
    die ("Connection failed: " . $db->connect_error);
}

// Check if data is received via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Decode the JSON object sent from JavaScript
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if the required fields are present in the received data
    if (isset ($data['student_id']) && isset ($data['ppass'])) {
        // Sanitize and validate input
        $student_id = mysqli_real_escape_string($db, $data['student_id']);
        $ppass = mysqli_real_escape_string($db, $data['ppass']);

        // Update the ppass column in the student table
        $query = "UPDATE student SET ppass = '$ppass' WHERE student_id = '$student_id'";

        if ($db->query($query) === TRUE) {
            $response = array('success' => true);
        } else {
            $response = array('success' => false, 'error' => $db->error);
        }
    } else {
        // Required fields are missing
        $response = array('success' => false, 'error' => 'Missing required fields');
    }
} else {
    // Invalid request method
    $response = array('success' => false, 'error' => 'Invalid request method');
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Close database connection
$db->close();
?>