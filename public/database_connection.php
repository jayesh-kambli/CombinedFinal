<?php

//database_connection.php
$connect = new PDO("mysql:host=localhost;dbname=attendance_tracker","root","");

$base_url = "http://localhost/ADMIN/";

function get_total_records($connect, $table_name)
{
 $query = "SELECT * FROM $table_name";
 $statement = $connect->prepare($query);
 $statement->execute();
 return $statement->rowCount();
}

function load_class_list($connect)
{
 $query = "
 SELECT * FROM class ORDER BY name ASC
 ";
 $statement = $connect->prepare($query);
 $statement->execute();
 $result = $statement->fetchAll();
 $output = '';
 foreach($result as $row)
 {
  $output .= '<option value="'.$row["class_id"].'">'.$row["name"].'</option>';
 }
 return $output;
}

function load_teacher_list($connect)
{
 $query = "
 SELECT * FROM teacher ORDER BY name ASC
 ";
 $statement = $connect->prepare($query);
 $statement->execute();
 $result = $statement->fetchAll();
 $output = '';
 foreach($result as $row)
 {
  $output .= '<option value="'.$row["teacher_id"].'">'.$row["name"].'</option>';
 }
 return $output;
}

function get_attendance_percentage($connect, $student_id)
{
 $query = "
 SELECT 
  ROUND((SELECT COUNT(*) FROM attendance 
  WHERE attendance_data = 'Present' 
  AND stud_id = '".$student_id."') 
 * 100 / COUNT(*)) AS percentage FROM attendance 
 WHERE stud_id = '".$student_id."'
 ";

 $statement = $connect->prepare($query);
 $statement->execute();
 $result = $statement->fetchAll();
 foreach($result as $row)
 {
  if($row["percentage"] > 0)
  {
   return $row["percentage"] . '%';
  }
  else
  {
   return 'NA';
  }
 }
}

function Get_student_name($connect, $student_id)
{
 $query = "
 SELECT student_name FROM student 
 WHERE student_id = '".$student_id."'
 ";

 $statement = $connect->prepare($query);

 $statement->execute();

 $result = $statement->fetchAll();

 foreach($result as $row)
 {
  return $row["name"];
 }
}

function Get_student_grade_name($connect, $student_id)
{
 $query = "
 SELECT class.name FROM student 
 INNER JOIN tbl_grade 
 ON class.class_id = student.clss_id
 WHERE student.student_id = '".$student_id."'
 ";
 $statement = $connect->prepare($query);
 $statement->execute();
 $result = $statement->fetchAll();
 foreach($result as $row)
 {
  return $row['name'];
 }
}

function Get_student_teacher_name($connect, $student_id)
{
 $query = "
 SELECT teacher.name 
 FROM student 
 INNER JOIN class 
 ON class.class_id = student.clss_id 
 INNER JOIN class 
 ON class.teacher = teacher.teacher_id 
 WHERE student.student_id = '".$student_id."'
 ";
 $statement = $connect->prepare($query);
 $statement->execute();
 $result = $statement->fetchAll();
 foreach($result as $row)
 {
  return $row["name"];
 }
}

function load_class_names($connect) {
    $output = '';
    $query = "SELECT name FROM class";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($result as $class_name) {
        $output .= '<option value="' . $class_name . '">' . $class_name . '</option>';
    }

    return $output;
}

function Get_grade_name($connect, $class_id)
{
 $query = "
 SELECT name FROM class
 WHERE class_id = '".$class_id."'
 ";
 $statement = $connect->prepare($query);
 $statement->execute();
 $result = $statement->fetchAll();
 foreach($result as $row)
 {
  return $row["name"];
 }
}

?>
