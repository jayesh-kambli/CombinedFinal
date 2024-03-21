<?php

//report.php
if(isset($_GET["action"]))
{
 include('database_connection.php');
 require_once 'pdf.php';
 session_start();
 $output = '';
 if($_GET["action"] == "student_report")
 {
  if(isset($_GET["student_id"], $_GET["from_date"], $_GET["to_date"]))
  {   
   $pdf = new Pdf();
   $query = "
   SELECT
                student.student_id,
                student.name AS student_name,
                student.rf_id,
				class.name AS class_name
            FROM 
                student
   INNER JOIN class
   ON class.class_id = student.student_class_id 
   WHERE student.student_id = '".$_GET["student_id"]."' 
   ";
   $statement = $connect->prepare($query);
   $statement->execute();
   $result = $statement->fetchAll();
   foreach($result as $row)
   {
    $output .= '
    <style>
    @page { margin: 20px; }
    
    </style>
    <p>&nbsp;</p>
    <h3 align="center">Attendance Report</h3><br /><br />
    <table width="100%" border="0" cellpadding="5" cellspacing="0">
           <tr>
               <td width="25%"><b>Student Name</b></td>
               <td width="75%">'.$row["student_name"].'</td>
           </tr>
           <tr>
               <td width="25%"><b>Roll Number</b></td>
               <td width="75%">'.$row["rf_id"].'</td>
           </tr>
           <tr>
               <td width="25%"><b>Grade</b></td>
               <td width="75%">'.$row["class_name"].'</td>
           </tr>
           <tr>
            <td colspan="2" height="5">
             <h3 align="center">Attendance Details</h3>
            </td>
           </tr>
           <tr>
            <td colspan="2">
             <table width="100%" border="1" cellpadding="5" cellspacing="0">
              <tr>
               <td><b>Attendance Date</b></td>
               <td><b>Attendance status</b></td>
              </tr>
    ';
    $sub_query = "
    SELECT * FROM attendance 
    WHERE student_id = '".$_GET["student_id"]."' 
    AND (attendance_date_time BETWEEN '".$_GET["from_date"]."' AND '".$_GET["to_date"]."') 
    ORDER BY attendance_date_time ASC
    ";
    $statement = $connect->prepare($sub_query);
    $statement->execute();
    $sub_result = $statement->fetchAll();
    foreach($sub_result as $sub_row)
    {
     $output .= '
      <tr>
       <td>'.$sub_row["attendance_date_time"].'</td>
       <td>'.$sub_row["attendance_data"].'</td>
      </tr>
     ';
    }
    $output .= '
      </table>
     </td>
     </tr>
    </table>
    ';
    $file_name = 'Attendance Report.pdf';
    $pdf->loadHtml($output);
    $pdf->render();
    $pdf->stream($file_name, array("Attachment" => false));
    exit(0);
   }
  }
 }

 if($_GET["action"] == "attendance_report")
 {
  if(isset($_GET["class_id"], $_GET["from_date"], $_GET["to_date"]))
  {
   $pdf = new Pdf();
   $query = "
   SELECT attendance.attendance_date_time FROM attendance 
   INNER JOIN student 
   ON student.student_id = attendance.stud_id 
   WHERE student.clss_id = '".$_GET["class_id"]."' 
   AND (attendance.attendance_date_time BETWEEN '".$_GET["from_date"]."' AND '".$_GET["to_date"]."')
   GROUP BY attendance.attendance_date_time 
   ORDER BY attendance.attendance_date_time ASC
   ";
   $statement = $connect->prepare($query);
   $statement->execute();
   $result = $statement->fetchAll();
   $output .= '
    <style>
    @page { margin: 20px; }
    
    </style>
    <p>&nbsp;</p>
    <h3 align="center">Attendance Report</h3><br />';
   foreach($result as $row)
   {
    $output .= '
    <table width="100%" border="0" cellpadding="5" cellspacing="0">
           <tr>
            <td><b>Date - '.$row["attendance_date_time"].'</b></td>
           </tr>
           <tr>
            <td>
             <table width="100%" border="1" cellpadding="5" cellspacing="0">
              <tr>
               <td><b>Student Name</b></td>
               <td><b>rf id</b></td>
               <td><b>class</b></td>
               <td><b>Teacher</b></td>
               <td><b>Attendance Status</b></td>
              </tr>
       ';
       $sub_query = "
       SELECT SELECT 
                Student.student_id,
                student.name AS student_name,
                student.rf_id,
                attendance.attendance_data,
                attendance.attendance_date_time,
				class.name AS class_name,
				teacher.name AS teacher_name
            FROM 
                attendance  
       INNER JOIN student 
       ON student.student_id = attendance.stud_id 
       INNER JOIN class 
       ON class.class_id = student.clss_id 
       ON class.teacher = teacher.teacher_id 
       WHERE student.clss_id = '".$_GET["class_id"]."' 
    AND attendance.attendance_date_time = '".$row["attendance_date_time"]."'
       ";
       $statement = $connect->prepare($sub_query);
    $statement->execute();
    $sub_result = $statement->fetchAll();
    foreach($sub_result as $sub_row)
    {
     $output .= '
     <tr>
      <td>'.$sub_row["student_name"].'</td>
      <td>'.$sub_row["rf_id"].'</td>
      <td>'.$sub_row["class_name"].'</td>
      <td>'.$sub_row["teacher_name"].'</td>
      <td>'.$sub_row["attendance_data"].'</td>
     </tr>
     ';
    }
    $output .= 
     '</table>
     </td>
     </tr>
    </table><br />';
   }
   $file_name = 'Attendance Report.pdf';
   $pdf->loadHtml($output);
   $pdf->render();
   $pdf->stream($file_name, array("Attachment" => false));
   exit(0);
  }
 }
}

?>