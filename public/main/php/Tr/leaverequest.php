<?php
require_once('../config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Extract values from the request body
$id = mysqli_real_escape_string($db, $data['id']);
$newStatus = mysqli_real_escape_string($db, $data['newStatus']);

// Make the SELECT query
$query = "SELECT leave_request FROM student WHERE student_id='$id'";
$result = mysqli_query($db, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $json_data = json_decode($row['leave_request'], true);

    // Find and update the specific request
    foreach ($json_data['requests'] as &$request) {
        if (
            $request['requestedAt'] == $data['requestedAt'] &&
            $request['from'] == $data['from'] &&
            $request['to'] == $data['to'] &&
            $request['reason'] == $data['reason'] &&
            $request['status'] == $data['status']
        ) {
            $request['status'] = (int)$newStatus;
            break;
        }
    }

    // Update the leave_request field in the database
    $updated_json_data = json_encode($json_data);
    $update_query = "UPDATE student SET leave_request='$updated_json_data' WHERE student_id='$id'";
    $update_result = mysqli_query($db, $update_query);

    if ($update_result) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Update failed"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Query failed"]);
}

$db->close();
?>
