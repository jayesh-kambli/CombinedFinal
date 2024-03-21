<?php
require_once('../config.php');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if "addType" is specified
if (isset($data['addType'])) {
    $addType = $data['addType'];

    // Process based on "addType"
    switch ($addType) {
        case 'student':
            addStudent($data);
            break;
        case 'teacher':
            addTeacher($data);
            break;
        case 'subject':
            addSubject($data);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid "addType" specified']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing "addType" in request']);
}

// Function to add a student
function addStudent($data)
{
    global $db;

    $student_id = $data['student_id'];
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $phone_no = $data['phone_no'];
    $clss_id = $data['clss_id'];

    // Use prepared statements to prevent SQL injection
    $checkPhoneQuery = $db->prepare("SELECT * FROM student WHERE phone_no = ?");
    $checkPhoneQuery->bind_param("i", $phone_no);
    $checkPhoneQuery->execute();
    $phoneResult = $checkPhoneQuery->get_result();

    $checkEmailQuery = $db->prepare("SELECT * FROM student WHERE email = ?");
    $checkEmailQuery->bind_param("s", $email);
    $checkEmailQuery->execute();
    $emailResult = $checkEmailQuery->get_result();

    if ($phoneResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
    } elseif ($emailResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email address already exists']);
    } else {
        $insertStudentQuery = $db->prepare("INSERT INTO student (student_id, name, email, password, phone_no, clss_id) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
        $insertStudentQuery->bind_param("issssi", $student_id, $name, $email, $password, $phone_no, $clss_id);

        if ($insertStudentQuery->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Student added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding student']);
        }

        $insertStudentQuery->close();  // Close the prepared statement
    }
    $checkPhoneQuery->close();
    $checkEmailQuery->close();
}



// Function to add a teacher
function addTeacher($data)
{
    global $db;

    $name = $data['name'];
    $phone_no = $data['phone_no'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
    $join_date = $data['join_date'];
    $subject = $data['subject'];

    $checkPhoneQuery = $db->prepare("SELECT * FROM teacher WHERE phone_no = ?");
    $checkPhoneQuery->bind_param("i", $phone_no);
    $checkPhoneQuery->execute();
    $phoneResult = $checkPhoneQuery->get_result();

    $checkEmailQuery = $db->prepare("SELECT * FROM teacher WHERE email = ?");
    $checkEmailQuery->bind_param("s", $email);
    $checkEmailQuery->execute();
    $emailResult = $checkEmailQuery->get_result();

    if ($phoneResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
    } elseif ($emailResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email address already exists']);
    } else {
        $insertTeacherQuery = $db->prepare("INSERT INTO teacher (name, phone_no, email, password, join_date, subject) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
        $insertTeacherQuery->bind_param("sissss", $name, $phone_no, $email, $password, $join_date, $subject);

        if ($insertTeacherQuery->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Teacher added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding teacher']);
        }
    }
    $checkPhoneQuery->close();
    $checkEmailQuery->close();
    $insertTeacherQuery->close();
}

// Function to add a subject
function addSubject($data)
{
    global $db;

    $name = $data['name'];
    $teacherId = $data['teacherId'];
    $classId = $data['classId'];

    $insertSubjectQuery = $db->prepare("INSERT INTO subject (name, teacher, class) 
                                       VALUES (?, ?, ?)");
    $insertSubjectQuery->bind_param("sii", $name, $teacherId, $classId);

    if ($insertSubjectQuery->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Subject added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding subject']);
    }
    $insertSubjectQuery->close();
}

$db->close();
?>