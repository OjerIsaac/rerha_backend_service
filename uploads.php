<?php

// header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:3001");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// Load DotEnvironment Class
require_once './classes/env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

require_once "./classes/classes.php";

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require 'Firebase-JWT/src/JWT.php';
require 'Firebase-JWT/src/Key.php';
require 'Firebase-JWT/src/SignatureInvalidException.php';
require 'Firebase-JWT/src/BeforeValidException.php';
require 'Firebase-JWT/src/ExpiredException.php';

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Handle upload
        if (empty($_POST)) {
            echo json_encode(array('success' => false, 'code' => 404, 'data' => array('message' => 'No data received')));
            exit();
        }
        else {
            // Verify token
            $jwt = $_SERVER["HTTP_AUTHORIZATION"];

            if (!$jwt) {
                echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Unauthorized access')));
                exit();
            }

            // Strip "Bearer " from the token
            $jwt = str_replace("Bearer ", "", $jwt);

            // Decode the token
            try {
                $decoded = JWT::decode($jwt, new Key($_ENV['KEY'], 'HS256'));
                // echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Invalid', 'token' => $decoded)));

                // Check if the token has expired
                if ($decoded->exp < time()) {
                    echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Token has expired')));
                    exit();
                }
                else {
                    // handle file upload

                    // Check the file size
                    if ($_FILES['file_name']['size'] > 10000000) {
                        echo json_encode(array('success' => false, 'code' => 400, 'data' => array("error" => "File size is too large. Maximum allowed size is 10 MB.")));
                        exit();
                    }

                    $validateImage = $user->validateImage($_FILES['file_name']['name']);
                    $uploadImage = $user->uploadImage($_FILES['file_name']['tmp_name']);

                    if ($validateImage && $uploadImage) {

                        $finalUpload = $user->finalUpload($uploadImage, $_POST['name'], $_POST['design_id'], $_POST['top'], $_POST['left'], $_POST['width'], $_POST['border_raduis_top_right'], $_POST['border_raduis_top_left'], $_POST['border_raduis_bottom_right'], $_POST['border_raduis_bottom_left'], $_POST['height'], $_POST['border_color'], $_POST['name_top'], $_POST['name_left'], $_POST['font_size'], $_POST['font_weight'], $_POST['font_color']);

                        if ($finalUpload) {
                            echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'Upload successful')));
                        } else {
                            echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'An error occurred')));
                            exit();
                        }
                    } else {
                        echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'Image file type not allowed')));
                        exit();
                    }
                    
                    // echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => $uploadImage)));
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Invalid Token')));
                exit();
            }
        }
        break;
    case 'GET':
        // Handle other operations
        echo json_encode(array('success' => false, 'code' => 505, 'data' => array('message' => 'Method not allowed')));
        break;
    default:
        // Handle invalid request methods
        echo json_encode(array('success' => false, 'code' => 505, 'data' => array('message' => 'Method not allowed')));
        break;
}


// json response
// {
//     "success": true,
//     "code": 200,
//     "data": {
//         "message": {
//             "name": "devFest_warri.jpg",
//             "full_path": "devFest_warri.jpg",
//             "type": "image/jpeg",
//             "tmp_name": "/tmp/php5IBlJF",
//             "error": 0,
//             "size": 118556
//         }
//     }
// }