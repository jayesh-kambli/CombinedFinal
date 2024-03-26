<?php
//student_action.php
include ('database_connection.php');
session_start();
$output = '';
if (isset ($_POST["action"])) {
    if ($_POST["action"] == 'fetch') {
        $query = "
        SELECT 
            student.student_id,
            student.name AS student_name,
            student.rf_id,
            student.email,
            student.phone_no,
            student.leave_request,
            class.name AS class_name
        FROM 
            student 
        INNER JOIN 
            class ON class.class_id = student.clss_id ";

            // Add class filter condition if a class is selected
 if(!empty($_POST["filter_class"]))
 {
     $query .= "WHERE student.clss_id = :filter_class ";
 }

 if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
    $query .= 'WHERE 
        (student.name LIKE "%' . $_POST["search"]["value"] . '%" 
        OR student.rf_id LIKE "%' . $_POST["search"]["value"] . '%" 
        OR student.email LIKE "%' . $_POST["search"]["value"] . '%" 
        OR student.phone_no LIKE "%' . $_POST["search"]["value"] . '%" 
        OR student.leave_request LIKE "%' . $_POST["search"]["value"] . '%" 
        OR class.name LIKE "%' . $_POST["search"]["value"] . '%") ';
}

        if (isset ($_POST["order"])) {
            $query .= 'ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else {
            $query .= 'ORDER BY student.student_id DESC ';
        }

        if ($_POST["length"] != -1) {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }
        $statement = $connect->prepare($query);
        if(!empty($_POST["filter_class"]))
        {
            $statement->bindValue(':filter_class', $_POST["filter_class"], PDO::PARAM_INT);
        }
        //$statement = $connect->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $data = array();
        $filtered_rows = $statement->rowCount();
        foreach ($result as $row) {
            $sub_array = array();
            $sub_array[] = $row["student_name"];
            $sub_array[] = $row["rf_id"];
            $sub_array[] = $row["email"];
            $sub_array[] = $row["class_name"];
            $sub_array[] = $row["phone_no"];
            // $sub_array[] = $row["leave_request"];

            $sub_array[] = '<button type="button" name="edit_student" class="btn btn-primary btn-sm edit_student" id="' . $row["student_id"] . '">Edit</button>';
            $sub_array[] = '<button type="button" name="delete_student" class="btn btn-danger btn-sm delete_student" id="' . $row["student_id"] . '">Delete</button>';
            $data[] = $sub_array;
        }

        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $filtered_rows,
            "recordsFiltered" => get_total_records($connect, 'student'),
            "data" => $data
        );
        echo json_encode($output);
    }

    if ($_POST["action"] == 'Add' || $_POST["action"] == "Edit") {
        $name = '';
        $rf_id = '';
        $email = '';
        $password = '';
        $phone_no = '';
        $leave_request = '';
        $clss_id = '';
        $error_name = '';
        $error_rf_id = '';
        $error_email = '';
        $error_password = '';
        $error_phone_no = '';
        $error_leave_request = '';
        $error_clss_id = '';
        $error = 0;


        if (empty ($_POST["name"])) {
            $error_name = 'Student Name is required';
            $error++;
        } else {
            $name = $_POST["name"];
        }
        if (empty ($_POST["rf_id"])) {
            $error_rf_id = 'Student rf id Number is required';
            $error++;
        } else {
            $rf_id = $_POST["rf_id"];
        }
        if ($_POST["action"] == "Add") {
            if (empty ($_POST["email"])) {
                $error_email = 'Email Address is required';
                $error++;
            } else {
                if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
                    $error_email = "Invalid email format";
                    $error++;
                } else {
                    $email = $_POST["email"];
                }
            }

            if (empty ($_POST["password"])) {
                $error_password = 'Password is required';
                $error++;
            } else {
                $password = $_POST["password"];
            }

            if (empty ($_POST["phone_no"])) {
                $error_phone_no = 'Teacher phone number is required';
                $error++;
            } else {
                $phone_no = $_POST["phone_no"];
            }
            if (empty ($_POST["leave_request"])) {
                $error_leave_request = 'enter leave request';
                $error++;
            } else {
                $leave_request = $_POST["leave_request"];
            }
            if (empty ($_POST["clss_id"])) {
                $error_clss_id = 'class is required';
                $error++;
            } else {
                $clss_id = $_POST["clss_id"];
            }

            if ($error > 0) {
                $output = array(
                    'error' => true,
                    'error_name' => $error_name,
                    'error_rf_id' => $error_rf_id,
                    'error_email' => $error_email,
                    'error_password' => $error_password,
                    'error_phone_no' => $error_phone_no,
                    'error_leave_request' => $error_leave_request,
                    'error_clss_id' => $error_clss_id
                );
            } else {
                if ($_POST["action"] == "Add") {
                    $data = array(
                        ':name' => $name,
                        ':rf_id' => $rf_id,
                        ':email' => $email,
                        ':password' => $password,
                        ':phone_no' => $phone_no,
                        ':leave_request' => '{"requests": []}',
                        ':clss_id' => $clss_id
                    );

                    // First, let's retrieve the end_id from the class table
                    $selectQuery = "SELECT start_id, end_id FROM class WHERE class_id = :clss_id";
                    $selectStatement = $connect->prepare($selectQuery);
                    $selectStatement->bindParam(':clss_id', $clss_id, PDO::PARAM_INT);
                    $selectStatement->execute();
                    $classRow = $selectStatement->fetch(PDO::FETCH_ASSOC);
                    $start_id = $classRow['start_id'];
                    $end_id = $classRow['end_id'];

                    // Prepare the attendance data
                    $atData = generateAttendanceData($start_id, $end_id);

                    $query = "
                        INSERT INTO student 
                        (name, rf_id, email, password, phone_no, leave_request, clss_id) 
                        VALUES (:name, :rf_id, :email, :password, :phone_no, :leave_request, :clss_id)
                        ";

                    $statement = $connect->prepare($query);
                    if ($statement->execute($data)) {
                        // Retrieve the ID of the last inserted row
                        $lastInsertedId = $connect->lastInsertId();

                        // Generate a unique 4-digit random attendance ID
                        do {
                            $random = sprintf("%04d", rand(0, 9999));
                            $checkQuery = "SELECT COUNT(*) as count FROM attendance WHERE attendance_id = :random";
                            $checkStatement = $connect->prepare($checkQuery);
                            $checkStatement->bindParam(':random', $random, PDO::PARAM_STR);
                            $checkStatement->execute();
                            $result = $checkStatement->fetch(PDO::FETCH_ASSOC);
                            $exists = $result['count'];
                        } while ($exists);

                        // Insert data into the attendance table
                        $attendanceQuery = "
                            INSERT INTO attendance 
                            (attendance_id, attendance_data, stud_id) 
                            VALUES (:random, :atData, :lastInsertedId)
                        ";

                        $atDataJson = json_encode($atData);
                        $attendanceStatement = $connect->prepare($attendanceQuery);
                        $attendanceStatement->bindParam(':random', $random, PDO::PARAM_STR);
                        $attendanceStatement->bindParam(':atData', $atDataJson, PDO::PARAM_STR);
                        $attendanceStatement->bindParam(':lastInsertedId', $lastInsertedId, PDO::PARAM_INT);
                        if ($attendanceStatement->execute()) {
                            $output = array(
                                'success' => 'Data Added Successfully',
                            );
                        } else {
                            $output = array(
                                'error' => true,
                                'error_message' => 'Failed to insert attendance data'
                            );
                        }
                    } else {
                        $output = array(
                            'error' => true,
                            'error_message' => 'Failed to insert student data'
                        );
                    }
                }
            }
            if ($_POST["action"] == "Edit") {
                $data = array(
                    ':name' => $name,
                    ':rf_id' => $rf_id,

                    ':phone_no' => $phone_no,
                    ':leave_request' => $leave_request,
                    ':clss_id' => $clss_id,
                    ':student_id' => $_POST["student_id"]
                );
                $query = "
                    UPDATE student 
                    SET name = :name, 
                    rf_id = :rf_id, 
                    phone_no = :phone_no, 
                    leave_request = :leave_request, 
                    clss_id = :clss_id, 
                    WHERE student_id = :student_id
                    ";
                $statement = $connect->prepare($query);
                if ($statement->execute($data)) {
                    $output = array(
                        'success' => 'Data Edited Successfully',
                    );
                }
            }
        }
        echo json_encode($output);
    }

    if ($_POST["action"] == "edit_fetch") {
        $query = "SELECT * FROM student WHERE student_id = '" . $_POST["student_id"] . "'";
        $statement = $connect->prepare($query);
        if ($statement->execute()) {
            $result = $statement->fetchAll();
            foreach ($result as $row) {
                $output['name'] = $row["name"];
                $output['rf_id'] = $row['rf_id'];
                //$output['email'] = $row['email'];
                $output['phone_no'] = $row['phone_no'];
                $output['leave_request'] = $row['leave_request'];
                $output['clss_id'] = $row['clss_id'];
                $output['student_id'] = $row['student_id'];
            }
            echo json_encode($output);
        }
    }

    if ($_POST["action"] == "delete") {
        try {
            // Begin a transaction
            $connect->beginTransaction();

            // Delete related attendance records
            $queryDeleteAttendance = "DELETE FROM attendance WHERE stud_id = :student_id";
            $statementDeleteAttendance = $connect->prepare($queryDeleteAttendance);
            $statementDeleteAttendance->bindValue(':student_id', $_POST["student_id"], PDO::PARAM_INT);
            $statementDeleteAttendance->execute();

            // Now you can safely delete the student record
            $queryDeleteStudent = "DELETE FROM student WHERE student_id = :student_id";
            $statementDeleteStudent = $connect->prepare($queryDeleteStudent);
            $statementDeleteStudent->bindValue(':student_id', $_POST["student_id"], PDO::PARAM_INT);
            $statementDeleteStudent->execute();

            // Commit the transaction
            $connect->commit();

            echo 'Data Deleted Successfully';
        } catch (PDOException $e) {
            // If an exception occurred, roll back the transaction
            $connect->rollBack();
            echo "Error deleting data: " . $e->getMessage();
        }
    }

}

function generateAttendanceData($startMonthYear, $endMonthYear)
{
    $startDate = strtotime(date('Y-m-01', strtotime($startMonthYear)));
    $endDate = strtotime(date('Y-m-01', strtotime($endMonthYear)));

    $data = [];
    while ($startDate <= $endDate) {
        $yearMonth = date('m-Y', $startDate);
        $daysInMonth = date('t', $startDate);

        $daysArray = array_fill(0, $daysInMonth, 0);
        $timesArray = array_fill(0, $daysInMonth, "00:00"); // Default time

        $data['atData'][] = [
            "yearMonth" => $yearMonth,
            "days" => $daysArray,
            "times" => $timesArray
        ];

        $startDate = strtotime("+1 month", $startDate);
    }

    return $data;
}

?>