<?php
require_once './db_connection.php';

header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PATCH, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    $title = $_POST['title'];
    $body = $_POST['content'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url'];
    $id = generateRandomString();
    // Validate the input
    if (empty($title) || empty($body)) {
        $response = [
            'error' => 'A blog must have at least a title and body.',
        ];
        http_response_code(400);
    } else {
        // Create the 'blogs' table if it doesn't exist
        // $createTableQuery = "CREATE TABLE IF NOT EXISTS blogs (
        //                         id INT AUTO_INCREMENT PRIMARY KEY,
        //                         title VARCHAR(255) NOT NULL UNIQUE,
        //                         body VARCHAR(2000) NOT NULL,
        //                         author VARCHAR(255) NULL,
        //                         category VARCHAR(255) NULL,
        //                         image_url VARCHAR(255) NULL,
        //                         created_date VARCHAR(255) NULL,
        //                     )";

        // // Assuming you already have a $connection variable established elsewhere in your code
        // if (!mysqli_query($connection, $createTableQuery)) {
        //     $response = [
        //         'error' => 'Error creating the blogs table: ' . mysqli_error($connection),
        //     ];
        //     http_response_code(500);
        //     exit();
        // }

        // Insert data into the database
        $query = "INSERT INTO blogs (id,title, body, author, category, image_url) VALUES ('$id','$title', '$body','$author','$category','$image_url')";

        try {
            $result = mysqli_query($connection, $query);

            if ($result) {
                $blogId = mysqli_insert_id($connection); // Retrieve the last inserted id
                $response = [
                    'message' => 'One blog item created successfully.',
                    'blogId' => $blogId,
                ];
                http_response_code(201);
            } else {
                $response = [
                    'error' => 'An error occurred while creating the blog item.',
                ];
                http_response_code(500);
            }
        } catch (mysqli_sql_exception $ex) {
            // Check if it's a duplicate entry error
            if ($ex->getCode() == 1062) {
                $response = [
                    'error' => 'A blog with this same title already exists. Please use a different title.',
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
    if (isset($_GET['id'])) {
        // Retrieve a specific blog by ID
        $blogId = $_GET['id'];
        $query = "SELECT * FROM blogs WHERE id = $blogId";
        $result = mysqli_query($connection, $query);

        if ($result) {
            $data = mysqli_fetch_assoc($result);
            if ($data) {
                $response = [
                    'data' => $data,
                ];
                http_response_code(200);
            } else {
                $response = [
                    'error' => 'Blog not found.',
                ];
                http_response_code(404);
            }
        } else {
            $response = [
                'error' => 'An error occurred while retrieving data.',
            ];
            http_response_code(500);
        }
    } else {
        // Retrieve all data from the 'blogs' table
        $query = "SELECT * FROM blogs";
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
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // Handle PATCH requests (update a specific blog by ID)

    parse_str(file_get_contents("php://input"), $patchData);

    if (isset($_GET['id'])) {
        $blogId = $_GET['id'];

        // Validate the input
        $title = $patchData['title'] ?? null;
        $body = $patchData['content'] ?? null;
        $author = $patchData['author'] ?? null;
        $image_url = $patchData['image'] ?? null;

        if (empty($title) && empty($body) && empty($author) && empty($image_url)) {
            $response = [
                'error' => 'No data provided for update.',
            ];
            http_response_code(400);
        } else {
            // Construct the update query
            $updateQuery = "UPDATE blogs SET ";
            $updateFields = [];

            if (!empty($title)) {
                $updateFields[] = "title = '$title'";
            }

            if (!empty($body)) {
                $updateFields[] = "body = '$body'";
            }

            if (!empty($author)) {
                $updateFields[] = "author = '$author'";
            }

            if (!empty($image_url)) {
                $updateFields[] = "image_url = '$image_url'";
            }

            $updateQuery .= implode(", ", $updateFields);
            $updateQuery .= " WHERE id = $blogId";

            // Execute the update query
            $result = mysqli_query($connection, $updateQuery);

            if ($result) {
                $response = [
                    'message' => 'Blog updated successfully.',
                ];
                http_response_code(200);
            } else {
                $response = [
                    'error' => 'An error occurred while updating the blog.',
                ];
                http_response_code(500);
            }
        }
    } else {
        $response = [
            'error' => 'No blog ID provided for update.',
        ];
        http_response_code(400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Handle DELETE requests (delete a specific blog by ID)

    parse_str(file_get_contents("php://input"), $deleteData);

    if (isset($_GET['id'])) {
        $blogId = $_GET['id'];

        // Construct the delete query
        $deleteQuery = "DELETE FROM blogs WHERE id = $blogId";

        // Execute the delete query
        $result = mysqli_query($connection, $deleteQuery);

        if ($result) {
            $response = [
                'message' => 'Blog deleted successfully.',
            ];
            http_response_code(200);
        } else {
            $response = [
                'error' => 'An error occurred while deleting the blog.',
            ];
            http_response_code(500);
        }
    } else {
        $response = [
            'error' => 'No blog ID provided for delete.',
        ];
        http_response_code(400);
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
