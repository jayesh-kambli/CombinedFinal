<?php

// attendance_action.php

include('database_connection.php');

session_start();

if(isset($_POST["action"]))
{
    if($_POST["action"] == "fetch")
    {
        $query = "
            SELECT 
                attendance.attendance_id,
                student.name AS student_name,
                student.rf_id,
                attendance.attendance_data,
                attendance.attendance_date_time,
                class.name AS class_name
            FROM 
                attendance 
            INNER JOIN student ON student.student_id = attendance.stud_id 
            INNER JOIN class ON class.class_id = student.clss_id
        ";
        
 // Add class filter condition if a class is selected
 if(!empty($_POST["filter_class"]))
 {
     $query .= "WHERE student.clss_id = :filter_class ";
 }


        if(isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"]))
        {
            $search_value = $_POST["search"]["value"];
            $query .= "
                WHERE 
                    student.name LIKE '%$search_value%' 
                    OR student.rf_id LIKE '%$search_value%' 
                    OR attendance.attendance_data LIKE '%$search_value%' 
                    OR attendance.attendance_date_time LIKE '%$search_value%'
            ";
        }

        if(isset($_POST["order"]))
        {
            $column_index = $_POST['order']['0']['column'];
            $column_name = $_POST['columns'][$column_index]['data'];
            $order_dir = $_POST['order']['0']['dir'];
            $query .= " ORDER BY $column_name $order_dir ";
        }
        else
        { 
            $query .= " ORDER BY attendance.attendance_id DESC ";
        }

        $limit = "";
        if($_POST["length"] != -1)
        {
            $start = $_POST['start'];
            $length = $_POST['length'];
            $limit = " LIMIT $start, $length";
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
        $todayDate = date('Y-m-d');
        foreach($result as $row)
        {
            $sub_array = array();
            // $status = ($row["attendance_data"] == "Present") ? '<label class="badge badge-success">Present</label>' : '<label class="badge badge-danger">Absent</label>';
            // $status = $row["attendance_data"];
            $status = (checkAttendance(json_decode($row["attendance_data"], true), $todayDate) == "Present") ? '<label class="badge badge-success">Present</label>' : '<label class="badge badge-danger">Absent</label>';
            $sub_array[] = $row["student_name"];
            $sub_array[] = $row["rf_id"];
            $sub_array[] = $row["class_name"];
            $sub_array[] = $status;
            $sub_array[] = $row["attendance_date_time"];
            $data[] = $sub_array;
        }

        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data), // Assuming all records are fetched
            "recordsFiltered" => count($data), // Same as total for now, modify as required
            "data" => $data
        );

        echo json_encode($output);
    }

    if($_POST["action"] == "index_fetch")
    {
        $query = "
            SELECT 
                student.student_id,
                student.name AS student_name,
                student.rf_id,
                class.name AS class_name
            FROM 
                student 
            LEFT JOIN attendance ON attendance.stud_id = student.student_id 
            INNER JOIN class ON class.class_id = student.clss_id 
        ";

// Add class filter condition if a class is selected
if(!empty($_POST["filter_class"]))
{
    $query .= "WHERE student.clss_id = :filter_class ";
}

        if(isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"]))
        {
            $search_value = $_POST["search"]["value"];
            $query .= "
                WHERE 
                    student.name LIKE '%$search_value%' 
                    OR student.rf_id LIKE '%$search_value%' 
                    OR class.name LIKE '%$search_value%'
            ";
        }

        $query .= " GROUP BY student.student_id ";

        if(isset($_POST["order"]))
        {
            $column_index = $_POST['order']['0']['column'];
            $column_name = $_POST['columns'][$column_index]['data'];
            $order_dir = $_POST['order']['0']['dir'];
            $query .= " ORDER BY $column_name $order_dir ";
        }
        else
        {
            $query .= " ORDER BY student.name ASC ";
        }

        $limit = "";
        if($_POST["length"] != -1)
        {
            $start = $_POST['start'];
            $length = $_POST['length'];
            $limit = " LIMIT $start, $length";
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
        foreach($result as $row)
        {
            $sub_array = array();
            $sub_array[] = $row["student_name"];
            $sub_array[] = $row["rf_id"];
            $sub_array[] = $row["class_name"];
            $sub_array[] = get_attendance_percentage($connect, $row["student_id"]);
            $sub_array[] = '<button type="button" name="report_button" data-student_id="'.$row["student_id"].'" class="btn btn-info btn-sm report_button">Report</button>&nbsp;&nbsp;&nbsp;<button type="button" name="chart_button" data-student_id="'.$row["student_id"].'" class="btn btn-danger btn-sm report_button">Chart</button>';
            $data[] = $sub_array;
        }

        $output = array(
            'draw' => intval($_POST["draw"]),
            "recordsTotal" => count($data), // Assuming all records are fetched
            "recordsFiltered" => count($data), // Same as total for now, modify as required
            "data" => $data
        );

        echo json_encode($output);
    }
}

function checkAttendance($data, $date) {
    $monthYear = date('m-Y', strtotime($date));
    foreach ($data['atData'] as $record) {
        if ($record['yearMonth'] == $monthYear) {
            // $dayIndex = (int)date('d', strtotime($date)) - 1;
            $dayIndex = date('d');
            if (isset($record['days'][$dayIndex])) {
                if ($record['days'][$dayIndex] == 1) {
                    return "Present";
                } elseif ($record['days'][$dayIndex] == 0) {
                    return "Absent";
                } elseif ($record['days'][$dayIndex] == 2) {
                    return "Holiday";
                }
            } else {
                return "Data not available for this date.";
            }
        }
    }
    return "Data not available";
}

?>