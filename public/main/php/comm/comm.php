<?php
require_once('../config.php');

// Sample database connection
$servername = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$dbname = DB_NAME;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch operation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data["type"] == "call" && isset($data["student_id"])) {
        $student_id = $data["student_id"];

        $sql = "SELECT comm FROM student WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode(array('success' => true, 'commData' => $row['comm']));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false));
        }
    }

    // Add operation
    elseif ($data["type"] == "add" && isset($data["student_id"], $data["newMessage"])) {
        $student_id = $data["student_id"];
        $newMessage = $data["newMessage"];

        $sql = "SELECT comm FROM student WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $commArray = json_decode($row["comm"], true);

            // Add new message to the communication array
            $commArray[] = $newMessage;

            // Update the database with the modified communication array
            $updatedComm = json_encode($commArray);

            $updateSql = "UPDATE student SET comm = ? WHERE student_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $updatedComm, $student_id);
            $updateStmt->execute();

            // Return the updated communication array
            $response["comm"] = $commArray;
            echo json_encode($response);
        } else {
            echo "No data found for the given student_id";
        }
    } else {
        echo "Invalid type or missing parameters";
    }
}

$conn->close();
?>
