<?php
require_once('config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Extract user ID and leave request data
$data = json_decode(file_get_contents('php://input'), true);
$infoType = $data['info'];
// $npass = $data['npass'];

if($infoType == 'sub') {
    //subject info =====>
    $classId = $data['classId'];
    $checkQuery = "SELECT * FROM subject WHERE class='$classId'";
    $checkResult = mysqli_query($db, $checkQuery);
    if (mysqli_num_rows($checkResult) > 0) {
        $res = mysqli_fetch_all($checkResult, MYSQLI_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'Got Data', 'data' => $res));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'No Such Subject'));
    }
} else if($infoType == 'assign') {
    //assignment info =====>
    $subid = $data['subid'];
    $checkQuery = "SELECT * FROM assignment WHERE sub_id='$subid'";
    $checkResult = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $res = mysqli_fetch_all($checkResult, MYSQLI_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'Got', 'data' => $res));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'No'));
    }
} else if($infoType == 'submit') {
    //submit info =====>
    $stuid = $data['stuid'];
    $assingId = $data['assingId'];
    $checkQuery = "SELECT * FROM submits WHERE stu_id=$stuid AND ass_id=$assingId";
    $checkResult = mysqli_query($db, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $res = mysqli_fetch_all($checkResult, MYSQLI_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'SubData', 'data' => $res));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'NoSubData'));
    }
} else if($infoType == 'classInfo') {
    //submit info =====>
    $classId = $data['classId'];
    $checkQuery = "SELECT * FROM class WHERE class_id=$classId";
    $checkResult = mysqli_query($db, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $res = mysqli_fetch_all($checkResult, MYSQLI_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'Got Data', 'data' => $res));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'No Class Data'));
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => 'Specify Info Type'));
}

$db->close();
?>