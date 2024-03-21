<?php
require_once('config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$assignment_id = $_POST['assignment_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;

// File upload section
$target_dir = "uploads/";
$original_file_name = $_FILES["file"]["name"] ?? null;
$file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);
$new_file_name = $student_id . '-' . $assignment_id . '-' . time() . '.' . $file_extension;
$target_file = $target_dir . $new_file_name;

if (move_uploaded_file($_FILES["file"]["tmp_name"] ?? null, $target_file)) {
    // File uploaded successfully, now update or insert data into the "submits" database

    // Check if data already exists for the student and assignment
    $check_query = "SELECT * FROM submits WHERE ass_id = $assignment_id AND stu_id = $student_id";
    $result = $db->query($check_query);

    if ($result && $result->num_rows > 0) {
        // Data already exists, update it (deletion part removed)
        $row = $result->fetch_assoc();
        $old_file_name = $row['fileName'];
        $old_file_path = $target_dir . $old_file_name;

        // Delete old file
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }

        $update_query = "UPDATE submits SET date = NOW(), fileName = '$new_file_name' WHERE ass_id = $assignment_id AND stu_id = $student_id";
        if ($db->query($update_query) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'File updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating file data']);
        }
    } else {
        // Data doesn't exist, insert new record
        $insert_query = "INSERT INTO submits (ass_id, stu_id, date, fileName) VALUES ($assignment_id, $student_id, NOW(), '$new_file_name')";
        if ($db->query($insert_query) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'File uploaded and data added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding file data']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error uploading file']);
}

// Close the database connection
$db->close();
?>