<?php
require_once('../config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Extract user ID and leave request data
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$name = $data['name'];
$phone = $data['phone'];
$class = $data['class'];

$checkQuery = "SELECT * FROM student WHERE student_id='$id'";
$checkResult = mysqli_query($db, $checkQuery);
if (mysqli_num_rows($checkResult) == 1) {
    $insertQuery = "UPDATE student SET name = '$name', phone_no = '$phone', clss_id = '$class' WHERE student_id = '$id'";
    if ($db->query($insertQuery) === TRUE) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'Data Changed'));
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