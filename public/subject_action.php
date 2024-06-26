<?php
include('database_connection.php');
session_start();
$output = array();

if(isset($_POST["action"])) {
    if($_POST["action"] == 'fetch') {
        $query = "SELECT subject.*, teacher.name AS teacher_name 
        FROM subject 
        LEFT JOIN teacher ON subject.teacher = teacher.teacher_id";
        if(isset($_POST["search"]["value"])) {
            $query .= ' WHERE subject.name LIKE "%' . $_POST["search"]["value"] . '%"
            OR teacher.name LIKE "%' . $_POST["search"]["value"] . '%"
';
        }
        if(isset($_POST["order"])) {
            $query .= ' ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'];
        }
        else {
            $query .= ' ORDER BY subject.subject_id DESC ';
        }
        if($_POST["length"] != -1) {
            $query .= ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $statement = $connect->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $data = array();
        $filtered_rows = $statement->rowCount();
        foreach($result as $row) {
            $sub_array = array();
            $sub_array[] = $row["name"];
            $sub_array[] = $row["teacher_name"]; // Assuming teacher name is fetched along with subject
            $sub_array[] = $row["class"];
            $sub_array[] = '<button type="button" name="view_subject" class="btn btn-info btn-sm view_subject" id="'.$row["subject_id"].'">View</button>';
            $sub_array[] = '<button type="button" name="edit_subject" class="btn btn-primary btn-sm edit_subject" id="'.$row["subject_id"].'">Edit</button>';
            $sub_array[] = '<button type="button" name="delete_subject" class="btn btn-danger btn-sm delete_subject" id="'.$row["subject_id"].'">Delete</button>';
            $data[] = $sub_array;
        }

        $output = array(
            "draw"              => intval($_POST["draw"]),
            "recordsTotal"      => $filtered_rows,
            "recordsFiltered"   => get_total_records($connect, 'subject'),
            "data"              => $data
        );
        echo json_encode($output);
    }

    if($_POST["action"] == 'Add' || $_POST["action"] == "Edit") {
        $name = $_POST["name"];
        $teacher = $_POST["teacher"];
        $class = $_POST["class"];

        $error_name = '';
        $error_teacher = '';
        $error_class = '';

        $error = 0;

        if(empty($name)) {
            $error_name = 'Subject Name is required';
            $error++;
        }

        if(empty($teacher)) {
            $error_teacher = 'Teacher is required';
            $error++;
        }

        if(empty($class)) {
            $error_class = 'Class is required';
            $error++;
        }

        // Check if the provided class exists in the class table
    $check_query = "SELECT COUNT(*) FROM class WHERE class_id = :class_id";
    $check_statement = $connect->prepare($check_query);
    $check_statement->bindParam(':class_id', $class, PDO::PARAM_INT);
    $check_statement->execute();
    $class_exists = $check_statement->fetchColumn();

    if($class_exists == 0) {
        $error_class = 'Invalid class selected';
        $error++;
    }

    if($error > 0) {
        $output = array(
            'error'         => true,
            'error_name'    => $error_name,
            'error_teacher' => $error_teacher,
            'error_class'   => $error_class
        );
    } else {
        $data = array(
            ':name'    => $name,
            ':teacher' => $teacher,
            ':class'   => $class
        );
        
        if($_POST["action"] == "Add") {
            $query = "INSERT INTO subject (name, teacher, class) VALUES (:name, :teacher, :class)";
        } elseif($_POST["action"] == "Edit") {
            $query = "UPDATE subject SET name = :name, teacher = :teacher, class = :class WHERE subject_id = :subject_id";
            $data[':subject_id'] = $_POST["subject_id"];
        }

        $statement = $connect->prepare($query);
        if($statement->execute($data)) {
            $output = array(
                'success' => ($_POST["action"] == "Add") ? 'Subject Added Successfully' : 'Subject Updated Successfully'
            );
        }
    }
    echo json_encode($output);
}

    if($_POST["action"] == 'single_fetch') {
        $query = "SELECT * FROM subject WHERE subject_id = '".$_POST["subject_id"]."'";
        $statement = $connect->prepare($query);
        if($statement->execute()) {
            $result = $statement->fetchAll();
            $output = '
                <div class="row">
            ';
            foreach($result as $row) {
                $output .= '
                    <div class="col-md-9">
                        <table class="table">
                            <tr>
                                <th>Subject Name</th>
                                <td>'.$row["name"].'</td>
                            </tr>
                            <tr>
                                <th>Teacher</th>
                                <td>'.$row["teacher"].'</td>
                            </tr>
                            <tr>
                                <th>Class</th>
                                <td>'.$row["class"].'</td>
                            </tr>
                        </table>
                    </div>
                ';
            }
            $output .= '</div>';
            echo $output;
        }
    }

    if($_POST["action"] == "edit_fetch") {
        $query = "SELECT * FROM subject WHERE subject_id = '".$_POST["subject_id"]."'";
        $statement = $connect->prepare($query);
        if($statement->execute()) {
            $result = $statement->fetchAll();
            foreach($result as $row) {
                $output['name'] = $row["name"];
                $output['teacher'] = $row['teacher'];
                $output['class'] = $row['class'];
                $output['subject_id'] = $row['subject_id'];
            }
            echo json_encode($output);
        }
    }

    if($_POST["action"] == "delete") {
        $query = "DELETE FROM subject WHERE subject_id = '".$_POST["subject_id"]."'";
        $statement = $connect->prepare($query);
        if($statement->execute()) {
            echo 'Subject Deleted Successfully';
        }
    }
}
?>
