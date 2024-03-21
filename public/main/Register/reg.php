<?php
// Simulate database connection
// Replace this with your actual database connection code
$db = mysqli_connect('localhost', 'root', '', 'tryDb');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Simulate checking if the username or email already exists
    $checkQuery = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $checkResult = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Send a JSON response with the appropriate header
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Username or email already exists'));
    } else {
        // Simulate inserting user data into the database
        $insertQuery = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        mysqli_query($db, $insertQuery);

        // Send a JSON response with the appropriate header
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'message' => 'Registration successful'));
    }
}
?>
