<?php
$connect = new PDO("mysql:host=localhost;dbname=attendance_tracker", "root", "");

// Get the raw JSON data from the request body & Decode the JSON data into an associative array
$jsonData = file_get_contents("php://input");
$data = json_decode($jsonData, true);

// Check if required keys are present
if (isset ($data['rfid']) && isset ($data['type'])) {
  $rfid = $data['rfid'];
  $type = $data['type'];

  if ($type == "getStuData") {

    // Fetch attendance data for the student with the given RFID value
    $query = "SELECT * FROM attendance WHERE stud_id = (
            SELECT student_id FROM student WHERE rf_id = :rfid
        )";

    $statement = $connect->prepare($query);
    $statement->execute(['rfid' => $rfid]);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return the result as JSON
    echo json_encode($result);
  } else if ($type == "putStuData") {
    $attendanceData = $data['data'];

    // Ensure 'attendanceData' is a string
    if (is_array($attendanceData)) {
      $attendanceData = json_encode($attendanceData);
    }

    // Update attendance_data for the student with the given RFID value
    $updateQuery = "UPDATE attendance SET attendance_data = :attendanceData WHERE stud_id = (
          SELECT student_id FROM student WHERE rf_id = :rfid
      )";

    $updateStatement = $connect->prepare($updateQuery);
    $updateResult = $updateStatement->execute(['attendanceData' => $attendanceData, 'rfid' => $rfid]);

    $query2 = "SELECT * FROM student WHERE rf_id = :rfid";
    $statement2 = $connect->prepare($query2);
    $statement2->execute(['rfid' => $rfid]);
    $result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);

    // Respond back about the success or any error
    if ($updateResult) {
      echo json_encode(['success' => true, 'message' => 'Attendance data updated successfully.', 'stData' => $result2]);
    } else {
      $errorInfo = $updateStatement->errorInfo();
      echo json_encode(['success' => false, 'message' => 'Failed to update attendance data.', 'error' => $errorInfo]);
    }
  } else if ($type == "getDate") {
       $sql = "SELECT c.end_id
              FROM class c
              INNER JOIN student s ON c.class_id = s.clss_id
              WHERE s.rf_id = :rfid";

      $stmt = $connect->prepare($sql);
      $stmt->bindParam(':rfid', $rfid);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($result !== false) {
        return $result['end_id'];
      } else {
        return null; // RFID not found or student not assigned to a class
      }

    // Return the result as JSON
    // echo json_encode($result);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
}
?>