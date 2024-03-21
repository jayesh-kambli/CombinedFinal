<?php
require_once('config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the data from the POST request
    $data = json_decode(file_get_contents("php://input"), true);
    // Access the value sent from JavaScript
    $receivedValue = $data['data'];
    $email = $data['user'];
    $pass = $data['pass'];

    // Get Attendanxce Data Data
    $query = "SELECT * FROM attendance WHERE stud_id='$receivedValue'";
    $result = mysqli_query($db, $query);

    //Get Student Data
    $query1 = "SELECT * FROM student WHERE email='$email' AND password='$pass'";
    $student = mysqli_query($db, $query1);

    if (($result && mysqli_num_rows($result) == 1) && ($student && mysqli_num_rows($student) == 1)) {
        $user = mysqli_fetch_assoc($result);
        $student = mysqli_fetch_assoc($student);
    
        // Send a JSON response with the appropriate header
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'attendance' => $user, 'student' => $student));
    } else {
        // Send a JSON response with the appropriate header
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Invalid username or password'));
    }
}
$db->close();
?>