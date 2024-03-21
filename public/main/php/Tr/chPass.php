<?php
require_once('../config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Extract user ID and leave request data
$data = json_decode(file_get_contents('php://input'), true);
$mail = $data['mail'];
$pass = $data['pass'];
$npass = $data['npass'];

$checkQuery = "SELECT * FROM teacher WHERE email='$mail' AND password='$pass'";
$checkResult = mysqli_query($db, $checkQuery);

//login
if (mysqli_num_rows($checkResult) == 1) {
    $insertQuery = "UPDATE teacher SET password = '$npass' WHERE email='$mail' AND password='$pass'";

    if ($db->query($insertQuery) === TRUE) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'Password Changed'));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Failed Server Side'));
    }
} else {
    //return
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => 'Wrong credentials'));
}

$db->close();
?>