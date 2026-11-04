<?php
header("Content-Type: application/json; charset=utf8");
echo json_encode(["message" => "Login successful"]); 

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Validate the username and password (this is just a simple example, you should implement proper validation)
        if ($username === 'admin' && $password === 'password') {
            echo json_encode(["message" => "Login successful"]);
            
        } else {
            echo json_encode(["message" => "Invalid username or password"]);
        }
    } else {
        echo json_encode(["message" => "Username and password are required"]);
    }

    ?>