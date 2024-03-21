<?php
require_once('../config.php');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch Classes
$queryClasses = "SELECT * FROM class";
$resultClasses = $db->query($queryClasses);

$classes = array();

if ($resultClasses) {
    while ($rowClass = $resultClasses->fetch_assoc()) {
        $class_id = $rowClass['class_id'];
        $class_name = $rowClass['name'];

        $students_query = "SELECT * FROM student WHERE clss_id = $class_id";
        $students_result = $db->query($students_query);
        $students = array();

        while ($student_row = $students_result->fetch_assoc()) {
            $students[] = array(
                'student_id' => $student_row['student_id'],
                'name' => $student_row['name'],
                'email' => $student_row['email'],
                'phone_no' => $student_row['phone_no']
                // Add other student fields as needed
            );
        }

        $subjects_query = "SELECT * FROM subject WHERE class = $class_id";
        $subjects_result = $db->query($subjects_query);
        $subjects = array();

        while ($subject_row = $subjects_result->fetch_assoc()) {
            $subjects[] = array(
                'subject_id' => $subject_row['subject_id'],
                'name' => $subject_row['name'],
                // Add other subject fields as needed
            );
        }

        $classes[] = array(
            'class_id' => $class_id,
            'class_name' => $class_name,
            'students' => $students,
            'subjects' => $subjects,
        );
    }
} else {
    // Handle query execution error for Classes
    echo json_encode(['status' => 'error', 'message' => 'Error executing the query for Classes']);
    exit;
}

// Fetch Teachers with Subjects
$queryTeachers = "SELECT * FROM teacher";
$resultTeachers = $db->query($queryTeachers);

$teachers = array();

if ($resultTeachers) {
    while ($rowTeacher = $resultTeachers->fetch_assoc()) {
        $teacher_id = $rowTeacher['teacher_id'];
        $teacher_name = $rowTeacher['name'];

        $subjects_query = "SELECT * FROM subject WHERE teacher = $teacher_id";
        $subjects_result = $db->query($subjects_query);
        $teacher_subjects = array();

        while ($subject_row = $subjects_result->fetch_assoc()) {
            $teacher_subjects[] = array(
                'subject_id' => $subject_row['subject_id'],
                'name' => $subject_row['name'],
                // Add other subject fields as needed
            );
        }

        $teachers[] = array(
            'teacher_id' => $teacher_id,
            'name' => $teacher_name,
            'phone_no' => $rowTeacher['phone_no'],
            'email' => $rowTeacher['email'],
            'subjects' => $teacher_subjects,
            'join_date' => $rowTeacher['join_date'],
        );
    }
} else {
    // Handle query execution error for Teachers
    echo json_encode(['status' => 'error', 'message' => 'Error executing the query for Teachers']);
    exit;
}

// Combine both results into a single response
$response = array(
    'status' => 'success',
    'classes' => $classes,
    'teachers' => $teachers
);

echo json_encode($response);

$db->close();
?>