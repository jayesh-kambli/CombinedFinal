<?php
// Include the config.php file
require_once('config.php');

// Get the raw POST data
$postData = file_get_contents('php://input');

// Decode the JSON data
$requestData = json_decode($postData);

// Check if the class ID is provided and is a valid integer
if (!isset($requestData->class_id) || !is_numeric($requestData->class_id)) {
    // Return error response if class ID is missing or invalid
    $response = array(
        'success' => false,
        'message' => 'Invalid class ID provided.'
    );
} else {
    // Extract the class ID from the decoded data
    $classId = $requestData->class_id;

    // Connect to the database
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if (!$db) {
        // Return error response if database connection fails
        $response = array(
            'success' => false,
            'message' => 'Database connection failed.'
        );
    } else {
        // Function to fetch teachers assigned to a class
        function getTeachersByClassId($classId, $conn) {
            $sql = "SELECT * FROM teacher WHERE teacher_id IN 
                    (SELECT DISTINCT teacher FROM subject WHERE class = $classId)";
            $result = mysqli_query($conn, $sql);

            $teachers = array();
            if (mysqli_num_rows($result) > 0) {
                // Output data of each row
                while ($row = mysqli_fetch_assoc($result)) {
                    $teachers[] = $row;
                }
            }

            return $teachers;
        }

        // Fetch teachers based on the class ID
        $teachers = getTeachersByClassId($classId, $db);

        // Close database connection
        mysqli_close($db);

        // Return success response with teachers data
        $response = array(
            'success' => true,
            'teachers' => $teachers
        );
    }
}

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
