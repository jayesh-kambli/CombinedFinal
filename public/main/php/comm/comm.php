<?php
require_once ('../config.php');

// Sample database connection
$servername = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$dbname = DB_NAME;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die ("Connection failed: " . $conn->connect_error);
}

// Fetch operation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data["type"] == "call" && isset ($data["student_id"])) {
        // $student_id = $data["student_id"];

        // $sql = "SELECT comm FROM student WHERE student_id = ?";
        // $stmt = $conn->prepare($sql);
        // $stmt->bind_param("i", $student_id);
        // $stmt->execute();
        // $result = $stmt->get_result();

        // if ($result->num_rows > 0) {
        //     $row = $result->fetch_assoc();
        //     header('Content-Type: application/json');
        //     echo json_encode(array('success' => true, 'commData' => $row['comm']));
        // } else {
        //     header('Content-Type: application/json');
        //     echo json_encode(array('success' => false));
        // }

        $student_id = $data["student_id"];
        $sender = $data['sender'];

        // Construct SQL query to select the "comm" field from the "student" table based on the provided student ID
        $sql = "SELECT comm FROM student WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there are any rows returned from the query
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Decode the JSON string to an array
            $commData = json_decode($row['comm'], true);

            // Mark all messages as read
            foreach ($commData as &$message) {
                if ($message['sender'] != $sender) {
                    $message['read'] = true;
                }
            }

            // Encode the modified array back to JSON
            $updatedCommData = json_encode($commData);

            // (to mark read) !!important
            // Update the "comm" column in the database with the modified JSON data 
            $updateSql = "UPDATE student SET comm = ? WHERE student_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $updatedCommData, $student_id);
            $updateStmt->execute();
            $_SESSION['prev_comm'] = $updatedCommData; // Update the session with the latest comm value [storing for comparing]

            // Return the updated communication data
            header('Content-Type: application/json');
            echo json_encode(array('success' => true, 'commData' => $updatedCommData));
        } else {
            // If no rows are found, return a failure status
            header('Content-Type: application/json');
            echo json_encode(array('success' => false));
        }
    }

    // Add operation
    elseif ($data["type"] == "add" && isset ($data["student_id"], $data["newMessage"])) {
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
    } elseif ($data["type"] == "check" && isset ($data["student_id"])) {
        $studentId = $data['student_id'];
        $sender = $data['sender'];
        $teacher = $data['teacher'];

        $sql = "SELECT comm FROM student WHERE student_id = $studentId";
        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();

            if ($row) {
                $comm = $row['comm'];
                session_start();
                $prevComm = $_SESSION['prev_comm']; // Get the previous comm value from session
                $_SESSION['prev_comm'] = $comm; // Update the session with the latest comm value

                // Check if the comm value has changed
                if ($comm !== $prevComm) {

                    $commData = json_decode($row['comm'], true);

                    $isChanged = false;
                    $ThisTr = false;
                    foreach ($commData as &$message) {
                        // echo ($message['sender'] != $sender ? "true" : "false") . "\n";
                        if ($message['sender'] != $sender) {
                            if ($message['teacher_id'] == $teacher) {
                                $message['read'] = true;
                            }
                        }
                    }


                    $updatedCommData = json_encode($commData);
                    $updateSql = "UPDATE student SET comm = ? WHERE student_id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("si", $updatedCommData, $studentId);
                    $updateStmt->execute();
                    $_SESSION['prev_comm'] = $updatedCommData; // Update the session with the latest comm value [storing for comparing]
                    $response = ['changed' => true, 'message' => $updatedCommData];
                } else {
                    $response = ['changed' => false, 'message' => 'No message changed'];
                }
            } else {
                $response = ['error' => 'Student ID not found'];
            }
        } else {
            $response = ['error' => 'Error executing query'];
        }

        // Return the result as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo "Invalid type or missing parameters";
    }
}

$conn->close();
?>