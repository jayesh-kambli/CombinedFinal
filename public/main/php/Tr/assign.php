<?php
require_once('../config.php');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the request body
    $json_data = file_get_contents('php://input');
    
    // Decode JSON data
    $data = json_decode($json_data, true);

    if ($data['type'] === 'add') {
        // Check if the assignment_id already exists
        $check_stmt = $db->prepare("SELECT * FROM assignment WHERE assignment_id = ?");
        $check_stmt->bind_param("i", $data['assignment_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Assignment_id already exists, respond accordingly
            echo json_encode(['success' => false, 'message' => 'Assignment ID already exists']);
        } else {
            // Assignment_id does not exist, proceed with insertion
            // Prepare and bind the SQL statement
            $insert_stmt = $db->prepare("INSERT INTO assignment (assignment_id, due_date, assignment_information, sub_id) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("isss", $data['assignment_id'], $data['due_date'], $data['assignment_information'], $data['sub_id']);

            // Execute the SQL statement
            if ($insert_stmt->execute() === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Assignment added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $insert_stmt->error]);
            }

            // Close the prepared statement for insertion
            $insert_stmt->close();
        }

        // Close the prepared statement for checking
        $check_stmt->close();
    } elseif ($data['type'] === 'del') {
        // Check if the assignment_id exists before attempting to delete
        $check_stmt = $db->prepare("SELECT * FROM assignment WHERE assignment_id = ?");
        $check_stmt->bind_param("i", $data['assignment_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Assignment_id exists, proceed with deletion
            // Prepare and bind the SQL statement for deletion
            $delete_stmt = $db->prepare("DELETE FROM assignment WHERE assignment_id = ?");
            $delete_stmt->bind_param("i", $data['assignment_id']);

            // Execute the SQL statement for deletion
            if ($delete_stmt->execute() === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $delete_stmt->error]);
            }

            // Close the prepared statement for deletion
            $delete_stmt->close();
        } else {
            // Assignment_id does not exist, respond accordingly
            echo json_encode(['success' => false, 'message' => 'Assignment ID does not exist']);
        }

        // Close the prepared statement for checking
        $check_stmt->close();
    } elseif ($data['type'] === 'subInfo') {
        // Retrieve all details from the 'submits' table based on assignment_id
        $sub_info_stmt = $db->prepare("SELECT * FROM submits WHERE ass_id = ?");
        $sub_info_stmt->bind_param("i", $data['assignment_id']);
        $sub_info_stmt->execute();
        $sub_info_result = $sub_info_stmt->get_result();

        if ($sub_info_result->num_rows > 0) {
            // Fetch and encode all data as JSON for response
            $sub_info_data = $sub_info_result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $sub_info_data]);
        } else {
            // No submissions found for the given assignment_id
            echo json_encode(['success' => false, 'message' => 'No submissions found for the given Assignment ID']);
        }

        // Close the prepared statement for 'subInfo'
        $sub_info_stmt->close();
    } elseif ($data['type'] === 'update') {
        // Check if the assignment_id exists before attempting to update
        $check_stmt = $db->prepare("SELECT * FROM assignment WHERE assignment_id = ?");
        $check_stmt->bind_param("i", $data['assignment_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Assignment_id exists, proceed with update
            // Prepare and bind the SQL statement for update
            $update_stmt = $db->prepare("UPDATE assignment SET due_date = ?, assignment_information = ? WHERE assignment_id = ?");
            $update_stmt->bind_param("ssi", $data['due_date'], $data['assignment_information'], $data['assignment_id']);

            // Execute the SQL statement for update
            if ($update_stmt->execute() === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $update_stmt->error]);
            }

            // Close the prepared statement for update
            $update_stmt->close();
        } else {
            // Assignment_id does not exist, respond accordingly
            echo json_encode(['success' => false, 'message' => 'Assignment ID does not exist for update']);
        }

        // Close the prepared statement for checking
        $check_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request type']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$db->close();
?>
