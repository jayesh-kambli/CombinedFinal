<?php
//student_action.php
include('database_connection.php');
session_start();
$output = array();

if (isset($_POST["action"])) {
    if ($_POST["action"] == 'fetch') {
        $query = "
        SELECT 
            student.student_id,
            student.name AS student_name,
            student.rf_id,
            student.email,
            student.phone_no,
            class.name AS class_name
        FROM 
            student 
        INNER JOIN 
            class ON class.class_id = student.clss_id ";

        // Add class filter condition if a class is selected
        if (!empty($_POST["filter_class"])) {
            $query .= "WHERE student.clss_id = :filter_class ";
        }

        if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
            $query .= 'WHERE 
                (student.name LIKE "%' . $_POST["search"]["value"] . '%" 
                OR student.rf_id LIKE "%' . $_POST["search"]["value"] . '%" 
                OR student.email LIKE "%' . $_POST["search"]["value"] . '%" 
                OR student.phone_no LIKE "%' . $_POST["search"]["value"] . '%" 
                OR class.name LIKE "%' . $_POST["search"]["value"] . '%") ';
        }

        if (isset($_POST["order"])) {
            $query .= 'ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else {
            $query .= 'ORDER BY student.student_id DESC ';
        }

        if ($_POST["length"] != -1) {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }
        $statement = $connect->prepare($query);
        if (!empty($_POST["filter_class"])) {
            $statement->bindValue(':filter_class', $_POST["filter_class"], PDO::PARAM_INT);
        }
        $statement->execute();
        $result = $statement->fetchAll();
        $data = array();
        $filtered_rows = $statement->rowCount();
        foreach ($result as $row) {
            $sub_array = array();
            $sub_array[] = $row["student_id"];
            $sub_array[] = $row["student_name"];
            $sub_array[] = $row["rf_id"];
            $sub_array[] = $row["email"];
            $sub_array[] = $row["class_name"];
            $sub_array[] = $row["phone_no"];

            $sub_array[] = '<button type="button" name="edit_student" class="btn btn-primary btn-sm edit_student" data-id="' . $row["student_id"] . '">Edit</button>';
            $sub_array[] = '<button type="button" name="delete_student" class="btn btn-danger btn-sm delete_student" data-id="' . $row["student_id"] . '">Delete</button>';
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
        $student_id = isset($_POST["student_id"]) ? $_POST["student_id"] : '';
        $name = $_POST["name"];
        $rf_id = $_POST["rf_id"];
        $email = isset($_POST["email"]) ? $_POST["email"] : '';
        $password = isset($_POST["password"]) ? $_POST["password"] : '';
        $phone_no = $_POST["phone_no"];
        $clss_id = $_POST["clss_id"];
        $error = 0;

        if (empty($name)) {
            $error++;
            $output['error_name'] = 'Student Name is required';
        }

        if (empty($rf_id)) {
            $error++;
            $output['error_rf_id'] = 'Student rf id Number is required';
        }

        if ($_POST["action"] == "Add") {
            if (empty($email)) {
                $error++;
                $output['error_email'] = 'Email Address is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error++;
                $output['error_email'] = 'Invalid email format';
            }

            if (empty($password)) {
                $error++;
                $output['error_password'] = 'Password is required';
            }

            if (empty($phone_no)) {
                $error++;
                $output['error_phone_no'] = 'Teacher phone number is required';
            }

            if (empty($clss_id)) {
                $error++;
                $output['error_clss_id'] = 'class is required';
            }
        }

        if ($error == 0) {
            $data = array(
                ':name' => $name,
                ':rf_id' => $rf_id,
                ':email' => $email,
                ':password' => $password,
                ':phone_no' => $phone_no,
                ':clss_id' => $clss_id
            );

            if ($_POST["action"] == "Add") {
                $query = "
                    INSERT INTO student 
                    (name, rf_id, email, password, phone_no, clss_id) 
                    VALUES (:name, :rf_id, :email, :password, :phone_no, :clss_id)
                ";
            } elseif ($_POST["action"] == "Edit") {
                $data[':student_id'] = $student_id;
                $query = "
                    UPDATE student 
                    SET name = :name, 
                    rf_id = :rf_id, 
                    email = :email, 
                    password = :password, 
                    phone_no = :phone_no, 
                    clss_id = :clss_id 
                    WHERE student_id = :student_id
                ";
            }

            $statement = $connect->prepare($query);
            if ($statement->execute($data)) {
                $output['success'] = ($_POST["action"] == "Add") ? 'Data Added Successfully' : 'Data Edited Successfully';
            } else {
                $output['error'] = true;
                $output['error_message'] = 'Failed to ' . ($_POST["action"] == "Add" ? 'add' : 'edit') . ' student data';
            }
        } else {
            $output['error'] = true;
        }
        echo json_encode($output);
    }

    if ($_POST["action"] == "edit_fetch") {
        if(isset($_POST["student_id"])) {
            $query = "SELECT * FROM student WHERE student_id = :student_id";
            $statement = $connect->prepare($query);
            $statement->bindValue(':student_id', $_POST["student_id"], PDO::PARAM_INT);
            if ($statement->execute()) {
                $result = $statement->fetchAll();
                foreach ($result as $row) {
                    $output['name'] = $row["name"];
                    $output['rf_id'] = $row['rf_id'];
                    //$output['email'] = $row['email'];
                    $output['phone_no'] = $row['phone_no'];
                    //$output['leave_request'] = $row['leave_request'];
                    $output['clss_id'] = $row['clss_id'];
                    $output['student_id'] = $row['student_id'];
                }
                echo json_encode($output);
            }
        } else {
            // Handle the case where student_id is not provided
            // You can echo an error message or handle it according to your requirements
            echo json_encode(["error" => "Student ID not provided"]);
        }
    }

    if ($_POST["action"] == "delete") {
        try {
            // Begin a transaction
            $connect->beginTransaction();
    
            // Check if 'student_id' key exists in $_POST array
            if (isset($_POST["student_id"])) {
                $student_id = $_POST["student_id"];
    
                // Delete related attendance records
                $queryDeleteAttendance = "DELETE FROM attendance WHERE stud_id = :student_id";
                $statementDeleteAttendance = $connect->prepare($queryDeleteAttendance);
                $statementDeleteAttendance->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                $statementDeleteAttendance->execute();
    
                // Now you can safely delete the student record
                $queryDeleteStudent = "DELETE FROM student WHERE student_id = :student_id";
                $statementDeleteStudent = $connect->prepare($queryDeleteStudent);
                $statementDeleteStudent->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                $statementDeleteStudent->execute();
    
                // Commit the transaction
                $connect->commit();
    
                echo 'Data Deleted Successfully';
            } else {
                echo 'Error: Student ID not provided.';
            }
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