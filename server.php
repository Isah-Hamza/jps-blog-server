<?php
require_once './db_connection.php';

header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];
    $body = $_POST['body'];
    $author = $_POST['author'];
    $image_url = $_POST['image'];
    $created_date = date("F j, Y");

    // Validate the input
    if (empty($title) || empty($body)) {
        $response = [
            'error' => 'A blog must have at least a title and body.',
        ];
        http_response_code(400);
    } else {
        // Create the 'blogs' table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS blogs (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                title VARCHAR(255) NOT NULL,
                                body VARCHAR(2000) NOT NULL,
                                author VARCHAR(255) NULL,
                                created_date VARCHAR(255) NULL,
                            )";

        // Assuming you already have a $connection variable established elsewhere in your code
        if (!mysqli_query($connection, $createTableQuery)) {
            $response = [
                'error' => 'Error creating the blogs table: ' . mysqli_error($connection),
            ];
            http_response_code(500);
            exit();
        }

        // Insert data into the database
        $query = "INSERT INTO blogs (title, body, author, image_url, created_at,) VALUES ('$title', '$body','$author','$image_url', '$created_at')";

        try {
            $result = mysqli_query($connection, $query);

            if ($result) {
                $userId = mysqli_insert_id($connection); // Retrieve the last inserted id
                $response = [
                    'message' => 'Waitlist user created successfully.',
                    'userId' => $userId,
                ];
                http_response_code(200);
            } else {
                $response = [
                    'error' => 'An error occurred while creating the waitlist user.',
                ];
                http_response_code(500);
            }
        } catch (mysqli_sql_exception $ex) {
            // Check if it's a duplicate entry error
            if ($ex->getCode() == 1062) {
                $response = [
                    'error' => 'Email address is already in use. Please use a different email.',
                ];
                http_response_code(400);
            } else {
                $response = [
                    'error' => 'An unexpected database error occurred.',
                ];
                http_response_code(500);
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET requests (retrieve all data)

    // Retrieve all data from the 'subscribers' table
    $query = "SELECT * FROM subscribers";
    $result = mysqli_query($connection, $query);

    if ($result) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $response = [
            'data' => $data,
        ];
        http_response_code(200);
    } else {
        $response = [
            'error' => 'An error occurred while retrieving data.',
        ];
        http_response_code(500);
    }
} else {
    $response = [
        'error' => 'Invalid request method.',
    ];
    http_response_code(405);
}

// Set the appropriate headers
header('Content-Type: application/json');

// Return the API response as JSON
echo json_encode($response);
exit;
