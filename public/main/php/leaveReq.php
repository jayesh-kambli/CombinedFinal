<?php
require_once('config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);

// Extract user ID and leave request data
$userId = $data['userId'];
$leaveReqDataArray = $data['data'];

// Prepare and execute the SQL query
$requests = json_encode(['requests' => $leaveReqDataArray]);
$sql = "UPDATE student SET leave_request = '$requests' WHERE student_id = $userId";

if ($db->query($sql) === TRUE) {
    header('Content-Type: application/json');
    echo json_encode(array('success' => true, 'message' => 'Request sent'));
} else {
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => 'Failed Server Side'));
}
// Close the database connection
// mysqli_close($db);


//example
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
//     $username = mysqli_real_escape_string($db, $_POST['username']);
//     $email = mysqli_real_escape_string($db, $_POST['email']);
//     $password = mysqli_real_escape_string($db, $_POST['password']);

//     // Simulate checking if the username or email already exists
//     $checkQuery = "SELECT * FROM users WHERE username='$username' OR email='$email'";
//     $checkResult = mysqli_query($db, $checkQuery);

//     if (mysqli_num_rows($checkResult) > 0) {
//         // Send a JSON response with the appropriate header
//         header('Content-Type: application/json');
//         echo json_encode(array('success' => false, 'message' => 'Username or email already exists'));
//     } else {
//         // Simulate inserting user data into the database
//         $insertQuery = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
//         mysqli_query($db, $insertQuery);

//         // Send a JSON response with the appropriate header
//         header('Content-Type: application/json');
//         echo json_encode(array('success' => true, 'message' => 'Registration successful'));
//     }
// }
$db->close();
?>