<?php
// Assuming you have a MySQL database set up
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data1 = $_POST["id"];
    $data2 = $_POST["date"];

    echo data1;
    echo data2;

    // Perform database query
    // $sql = "INSERT INTO your_table_name (column_name) VALUES ('$data')";

    // if ($conn->query($sql) === TRUE) {
    //     $response = array("status" => "success", "message" => "Data inserted successfully");
    // } else {
    //     $response = array("status" => "error", "message" => "Error: " . $sql . "<br>" . $conn->error);
    // }

    // Close connection
    $conn->close();

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
