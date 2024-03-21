<?php
include('database_connection.php');
session_start();
$output = array();

if(isset($_POST["action"])) {
    if($_POST["action"] == 'fetch') {
        $query = "SELECT * FROM subject ";
        if(isset($_POST["search"]["value"])) {
            $query .= 'WHERE name LIKE "%'.$_POST["search"]["value"].'%"';
        }
        if(isset($_POST["order"])) {
            $query .= ' ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'];
        }
        else {
            $query .= ' ORDER BY subject_id DESC ';
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
            $sub_array[] = $row["teacher"]; // Assuming teacher name is fetched along with subject
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
        $name = '';
        $teacher_id = '';
        $class = '';

        $error_name = '';
        $error_teacher_id = '';
        $error_class = '';

        $error = 0;

        if(empty($_POST["name"])) {
            $error_name = 'Subject Name is required';
            $error++;
        } else {
            $name = $_POST["name"];
        }

        if(empty($_POST["teacher_id"])) {
            $error_teacher_id = 'Teacher is required';
            $error++;
        } else {
            $teacher_id = $_POST["teacher_id"];
        }

        if(empty($_POST["class"])) {
            $error_class = 'Class is required';
            $error++;
        } else {
            $class = $_POST["class"];
        }

        if($error > 0) {
            $output = array(
                'error'             => true,
                'error_name'=> $error_name,
                'error_teacher_id'  => $error_teacher_id,
                'error_class'       => $error_class
            );
        } else {
            $data = array(
                ':name' => $name,
                ':teacher_id'   => $teacher_id,
                ':class'        => $class
            );
            
            if($_POST["action"] == "Add") {
                $query = "INSERT INTO subject (name, teacher_id, class) VALUES (:name, :teacher_id, :class)";
            } elseif($_POST["action"] == "Edit") {
                $query = "UPDATE subject SET name = :name, teacher_id = :teacher_id, class = :class WHERE subject_id = :subject_id";
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
                $output['teacher_id']   = $row['teacher_id'];
                $output['class']        = $row['class'];
                $output['subject_id']   = $row['subject_id'];
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
