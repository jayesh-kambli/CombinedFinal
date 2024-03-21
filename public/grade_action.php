<?php

//grade_action.php

include('database_connection.php');
session_start();
$output = array(); // Initialize $output as an array
if(isset($_POST["action"]))
{
    if($_POST["action"] == 'fetch')
    {
        $query = "SELECT * FROM class ";
        if(isset($_POST["search"]["value"]))
        {
            $query .= 'WHERE class.name LIKE "%'.$_POST["search"]["value"].'%"
            ';
        }
        if(isset($_POST["order"]))
        {
            $query .= 'ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'].' ';
        }
        else
        {
            $query .= 'ORDER BY class_id DESC ';
        }
        if($_POST["length"] != -1)
        {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $statement = $connect->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $data = array();
        $filtered_rows = $statement->rowCount();
        foreach($result as $row)
        {
            $sub_array = array();
            $sub_array[] = $row["name"];
            $sub_array[] = $row["teacher"];
            $sub_array[] = '<button type="button" name="edit_grade" class="btn btn-primary btn-sm edit_grade" id="'.$row["class_id"].'">Edit</button>';
            $sub_array[] = '<button type="button" name="delete_grade" class="btn btn-danger btn-sm delete_grade" id="'.$row["class_id"].'">Delete</button>';
            $data[] = $sub_array;
        }

        $output = array(
            "draw"    => intval($_POST["draw"]),
            "recordsTotal"  =>  $filtered_rows,
            "recordsFiltered" => get_total_records($connect, 'class'),
            "data"    => $data
        );
        echo json_encode($output);
    }

    if($_POST["action"] == 'Add' || $_POST["action"] == "Edit")
    {
        $name = '';
        $teacher = '';
        $error_name = '';
        $error_teacher = '';
        $error = 0;

        if(empty($_POST["name"]))
        {
            $error_name = 'Class Name is required';
            $error++;
        }
        else
        {
            $name = $_POST["name"];
        }
        if(empty($_POST["teacher"]))
        {
            $error_teacher= 'Teacher is required';
            $error++;
        }
        else
        {
            $teacher = $_POST["teacher"];
        }
        
        // Check if teacher is already assigned to another class
        $query = "SELECT * FROM class WHERE teacher = :teacher AND class_id != :class_id";
        $statement = $connect->prepare($query);
        $statement->execute(array(':teacher' => $teacher, ':class_id' => $_POST["class_id"]));
        $teacher_assigned = $statement->fetch();
        
        if($teacher_assigned)
        {
            $output = array(
                'error'       => true,
                'error_teacher'    => 'Teacher is already assigned to another class'
            );
        }
        elseif($error > 0)
        {
            $output = array(
                'error'       => true,
                'error_name'    => $error_name,
                'error_teacher'    => $error_teacher
            );
        }
        else
        {
            if($_POST["action"] == "Add")
            {
                $data = array(
                    ':name'    => $name,
                    ':teacher'    => $teacher
                );
                $query = "
                    INSERT INTO class
                    (name, teacher) 
                    SELECT * FROM (SELECT :name, :teacher) as temp 
                    WHERE NOT EXISTS (
                        SELECT name FROM class WHERE name = :name
                    ) LIMIT 1
                ";
                $statement = $connect->prepare($query);
                if($statement->execute($data))
                {
                    if($statement->rowCount() > 0)
                    {
                        $output = array(
                            'success'  => 'Data Added Successfully',
                        );
                    }
                    else
                    {
                        $output = array(
                            'error'     => true,
                            'error_name' => 'Class Name Already Exists'
                        );
                    }
                }
            }
            if($_POST["action"] == "Edit")
            {
                $data = array(
                    ':name'    => $name,
                    ':teacher'    => $teacher,
                    ':class_id'     => $_POST["class_id"]
                );
                $query = "
                    UPDATE class 
                    SET name = :name ,
                    teacher = :teacher
                    WHERE class_id = :class_id
                ";
                $statement = $connect->prepare($query);
                if($statement->execute($data))
                {
                    $output = array(
                        'success'  => 'Data Updated Successfully',
                    );
                }
            }
        }
        echo json_encode($output);
    }

    if($_POST["action"] == "edit_fetch")
    {
        $query = "SELECT * FROM class WHERE class_id = '".$_POST["class_id"]."'";
        $statement = $connect->prepare($query);
        if($statement->execute())
        {
            $result = $statement->fetchAll();
            foreach($result as $row)
            {
                $output['name'] = $row["name"];
                $output['teacher'] = $row["teacher"];
                $output['class_id'] = $row['class_id'];
            }
            echo json_encode($output);
        }
    }

    if($_POST["action"] == "delete")
    {
        $query = "DELETE FROM class WHERE class_id = '".$_POST["class_id"]."'";
        $statement = $connect->prepare($query);
        if($statement->execute())
        {
            echo 'Data Delete Successfully';
        }
    }

    // Fetch existing class names for dropdown
    if($_POST["action"] == "get_class_names")
    {
        $query = "SELECT name FROM class";
        $statement = $connect->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($result);
    }
}

?>
