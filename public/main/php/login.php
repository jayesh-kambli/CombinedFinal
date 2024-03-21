<?php
require_once('config.php');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Check if the email ends with '@tr.com' to determine if it's a teacher
    if (strpos($username, '@tr.com') !== false) {
        $query = "SELECT * FROM teacher WHERE email='$username' AND password='$password'";
    } else {
        $query = "SELECT * FROM student WHERE email='$username' AND password='$password'";
    }

    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Send a JSON response with the appropriate header
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'user' => $user, 'message' => 'Login successful'));
    } else {
        // Send a JSON response with the appropriate header
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Invalid username or password'));
    }

    // Close the prepared statement
    mysqli_stmt_close($stmt);
}

// Close the database connection
$db->close();
?>
