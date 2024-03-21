<?php
include('database_connection.php');
session_start();
$output = array();
if(isset($_POST["action"])) {
    if($_POST["action"] == 'fetch') {
        $query = "SELECT * FROM teacher ";
        if(isset($_POST["search"]["value"])) {
            $query .= 'WHERE teacher.name LIKE "%'.$_POST["search"]["value"].'%" 
                        OR teacher.email LIKE "%'.$_POST["search"]["value"].'%"';
        }
        if(isset($_POST["order"])) {
            $query .= ' ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'];
        }
        else {
            $query .= ' ORDER BY teacher.teacher_id DESC ';
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
            $sub_array[] = $row["email"];
            $sub_array[] = '<button type="button" name="view_teacher" class="btn btn-info btn-sm view_teacher" id="'.$row["teacher_id"].'">View</button>';
            $sub_array[] = '<button type="button" name="edit_teacher" class="btn btn-primary btn-sm edit_teacher" id="'.$row["teacher_id"].'">Edit</button>';
            $sub_array[] = '<button type="button" name="delete_teacher" class="btn btn-danger btn-sm delete_teacher" id="'.$row["teacher_id"].'">Delete</button>';
            $data[] = $sub_array;
        }

        $output = array(
            "draw"              => intval($_POST["draw"]),
            "recordsTotal"      => $filtered_rows,
            "recordsFiltered"   => get_total_records($connect, 'teacher'),
            "data"              => $data
        );
        echo json_encode($output);
    }
	 if($_POST["action"] == 'Add' || $_POST["action"] == "Edit")
 {
  $name = '';
  $phone_no = '';
  $email = '';
  $password = '';

  $subject = '';
  $join_date = '';
 
  $error_name = '';
  $error_phone_no = '';
  $error_email = '';
  $error_password = '';

  $error_subject = '';
  $error_join_date = '';
 
  $error = 0;

 
  if(empty($_POST["name"]))
  {
   $error_name = 'Teacher Name is required';
   $error++;
  }
  else
  {
   $name = $_POST["name"];
  }
  if(empty($_POST["phone_no"]))
  {
   $error_phone_no = 'Teacher phone number is required';
   $error++;
  }
  else
  {
   $phone_no = $_POST["phone_no"];
  }
  if($_POST["action"] == "Add")
  {
   if(empty($_POST["email"]))
   {
    $error_email = 'Email Address is required';
    $error++;
   }
   else
   {
    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
    {
          $error_email = "Invalid email format"; 
          $error++;
       }
       else
       {
     $email = $_POST["email"];
    }
   }
  
   if(empty($_POST["password"]))
   {
    $error_password = 'Password is required';
    $error++;
   }
   else
   {
    $password = $_POST["password"];
   }
  }

  

  if(empty($_POST["subject"]))
  {
   $error_subject = 'subject Field is required';
   $error++;
  }
  else
  {
   $subject = $_POST["subject"];
  }
  if(empty($_POST["join_date"]))
  {
   $error_join_date = 'Date of Join Field is required';
   $error++;
  }
  else
  {
   $join_date= $_POST["join_date"];
  }
  if($error > 0)
  {
   $output = array(
    'error'       => true,
    'error_name'   => $error_name,
    'error_phone_no'   => $error_phone_no,
    'error_email'   => $error_email,
    'error_password'  => $error_password,
 
    'error_subject' => $error_subject,
    'error_join_date'    => $error_join_date,

   );
   $selected_subjects = isset($_POST["subjects"]) ? $_POST["subjects"] : array();
   $subject_ids = array();
   foreach($selected_subjects as $subject_id) {
       // Ensure subject_id is valid and exists in the subject table
       // You may need to modify this part based on your actual database schema
       $subject_id = intval($subject_id);
       $query = "SELECT subject_id FROM subject WHERE subject_id = :subject_id";
       $statement = $connect->prepare($query);
       $statement->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
       $statement->execute();
       if($statement->rowCount() > 0) {
           $subject_ids[] = $subject_id;
       }
   }
   // Now $subject_ids contains valid subject_ids selected by the teacher
   // You can store $subject_ids in your database accordingly

  }
  else
  {
   if($_POST["action"] == "Add")
   {
    $data = array(
     ':name'    => $name,
     ':phone_no'   => $phone_no,
     ':email'   => $email,
     ':password'   => password_hash($password, PASSWORD_DEFAULT),
     ':subject' => $subject,
     ':join_date'    => $join_date,

    );
    $query = "
    INSERT INTO teacher 
    (name, phone_no, email, password, subject, join_date) 
    SELECT * FROM (SELECT :name, :phone_no, :email, :password, :subject, :join_date) as temp 
    WHERE NOT EXISTS (
     SELECT email FROM teacher WHERE email = :email
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
       'error_email' => 'Email Already Exists'
      );
     }
    }
   }
   if($_POST["action"] == "Edit")
{
    $data = array(
        ':name'         => $name,
        ':phone_no'     => $phone_no,
        ':subject'      => $subject,
        ':join_date'    => $join_date,
        ':teacher_id'   => $_POST["teacher_id"]
    );
   $query = "
    UPDATE teacher 
    SET name = :name, 
    phone_no = :phone_no,  
    subject = :subject, 
    join_date = :join_date
    WHERE teacher_id = :teacher_id
";
    $statement = $connect->prepare($query);
    if($statement->execute($data))
    {
        $output = array(
            'success'  => 'Data Edited Successfully',
        );
    }
}
  }
  echo json_encode($output);
 }

    if($_POST["action"] == 'single_fetch') {
        $query = "SELECT * FROM teacher WHERE teacher_id = '".$_POST["teacher_id"]."'";
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
                                <th>Name</th>
                                <td>'.$row["name"].'</td>
                            </tr>
                            <tr>
                                <th>phone number</th>
                                <td>'.$row["phone_no"].'</td>
                            </tr>
                            <tr>
                                <th>Email Address</th>
                                <td>'.$row["email"].'</td>
                            </tr>
                            <tr>
                                <th>subject</th>
                                <td>'.$row["subject"].'</td>
                            </tr>
                            <tr>
                                <th>Date of Joining</th>
                                <td>'.$row["join_date"].'</td>
                            </tr>
                        </table>
                    </div>
                ';
            }
            $output .= '</div>';
            echo $output;
        }
    }


if($_POST["action"] == "edit_fetch")
{
    $query = "SELECT * FROM teacher WHERE teacher_id = '".$_POST["teacher_id"]."'";
    $statement = $connect->prepare($query);
    if($statement->execute())
    {
        $result = $statement->fetchAll();
        foreach($result as $row)
        {
            $output['name'] = $row["name"];
            $output['phone_no'] = $row['phone_no'];
            //$output['email'] = $row['email'];
            $output['subject'] = $row['subject'];
            $output['join_date'] = $row['join_date'];

            $output['teacher_id'] = $row['teacher_id'];
        }
        echo json_encode($output);
    }
}

 if($_POST["action"] == "delete")
 {
  $query = "DELETE FROM teacher WHERE teacher_id = '".$_POST["teacher_id"]."'";
  $statement = $connect->prepare($query);
  if($statement->execute())
  {
   echo 'Data Delete Successfully';
  }
 }
}

?>