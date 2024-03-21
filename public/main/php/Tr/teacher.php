<?php
require_once('../config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['user'];
    $pass = $data['pass'];

    // Get Teacher Data
    $query1 = "SELECT * FROM teacher WHERE email='$email' AND password='$pass'";
    $teacher = mysqli_query($db, $query1);

    if ($teacher && mysqli_num_rows($teacher) == 1) {
        $teacherData = mysqli_fetch_assoc($teacher);
        $teacherId = $teacherData['teacher_id'];

        // Command to get subjects related to the teacher
        $subjectQuery = "SELECT * FROM subject WHERE teacher='$teacherId'";
        $subjectResult = mysqli_query($db, $subjectQuery);

        // Response array for subjects
        $subjectsResponse = array();
        $attendanceIdsAll = array();

        while ($subjectData = mysqli_fetch_assoc($subjectResult)) {
            $subjectId = $subjectData['subject_id'];

            // Command to get assignments related to the subject
            $assignmentQuery = "SELECT * FROM assignment WHERE sub_id='$subjectId'";
            $assignmentResult = mysqli_query($db, $assignmentQuery);

            // Response array for assignments
            $assignmentsResponse = array();

            while ($assignmentData = mysqli_fetch_assoc($assignmentResult)) {
                $assignmentsResponse[] = $assignmentData;
                $attendanceIdsAll[] = $assignmentData['assignment_id'];
            }

            $subjectData['assignments'] = $assignmentsResponse;
            $subjectsResponse[] = $subjectData;
        }

        // Command to get classes related to the teacher
        $classQuery = "SELECT * FROM class WHERE teacher='$teacherId'";
        $classResult = mysqli_query($db, $classQuery);

        // Response array for classes
        $classesResponse = array();

        while ($classData = mysqli_fetch_assoc($classResult)) {
            $classId = $classData['class_id'];

            // Command to get students related to the class
            $studentQuery = "SELECT * FROM student WHERE clss_id='$classId'";
            $studentResult = mysqli_query($db, $studentQuery);

            // Response array for students
            $studentsResponse = array();

            while ($studentData = mysqli_fetch_assoc($studentResult)) {
                $studentId = $studentData['student_id'];

                // Command to get attendance related to the student
                $attendanceQuery = "SELECT * FROM attendance WHERE stud_id='$studentId'";
                $attendanceResult = mysqli_query($db, $attendanceQuery);

                // Response array for attendance
                $attendanceResponse = array();

                while ($attendanceData = mysqli_fetch_assoc($attendanceResult)) {
                    $attendanceResponse[] = $attendanceData;
                }

                $studentData['attendance'] = $attendanceResponse;
                $studentsResponse[] = $studentData;
            }

            $classData['students'] = $studentsResponse;
            $classesResponse[] = $classData;
        }

        $query = "SELECT * FROM class";
        $result = mysqli_query($db, $query);
        
        if ($result) {
            $allClassesInDb = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $allClassesInDb = "Not Found class";
        }

        // Final response array
        $finalResponse = array(
            'success' => true,
            'dataTr' => $teacherData,
            'subjects' => $subjectsResponse,
            'attendanceIdsAll' => $attendanceIdsAll,
            'classes' => $classesResponse,
            'allClassInDb'=> $allClassesInDb
        );

        header('Content-Type: application/json');
        echo json_encode($finalResponse);
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Failed To Access Data'));
    }
}

$db->close();
?>