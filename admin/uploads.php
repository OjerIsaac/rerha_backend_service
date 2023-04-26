<?php

header("Access-Control-Allow-Origin: http://localhost:3000, https://rerhadp.vercel.app");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header('Content-Type: application/json');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

// Load DotEnvironment Class
require_once '../classes/env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

require_once "../classes/classes.php";

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require '../Firebase-JWT/src/JWT.php';
require '../Firebase-JWT/src/Key.php';
require '../Firebase-JWT/src/SignatureInvalidException.php';
require '../Firebase-JWT/src/BeforeValidException.php';
require '../Firebase-JWT/src/ExpiredException.php';

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $required_fields = array('name', 'design_id', 'top', 'left', 'width', 'border', 'border_raduis_top_right', 'border_raduis_top_left', 'border_raduis_bottom_right', 'border_raduis_bottom_left', 'height', 'border_color', 'name_top', 'name_left', 'font_size', 'font_weight', 'font_color');
        $empty_fields = array();

        foreach ($required_fields as $field) {
            if (empty($_REQUEST[$field])) {
                $empty_fields[] = $field;
            }
        }
        if (!empty($empty_fields)) {
            echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'This fields '.implode(', ', $empty_fields).' cannot be empty'  )));
            exit();
        } else {
            // Verify token
            $jwt = $_SERVER["HTTP_AUTHORIZATION"];

            if (!$jwt) {
                echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Unauthorized access')));
                exit();
            }

            // Strip "Bearer " from the token
            $jwt = str_replace("Bearer ", "", $jwt);

            try {
                // Decode the token
                $decoded = JWT::decode($jwt, new Key($_ENV['KEY'], 'HS256'));

                // Check if the token has expired
                if ($decoded->exp < time()) {
                    echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Token has expired')));
                    exit();
                }else {
                    isset($_FILES['file']['name']) ? $image = $_FILES['file']['name'] : $image = null;

                    // Check the file size
                    if ($_FILES['file']['size'] > 10000000) {
                        echo json_encode(array('success' => false, 'code' => 400, 'data' => array("error" => "File size is too large. Maximum allowed size is 10 MB.")));
                        exit();
                    }

                    $validateImage = $user->validateImage($image); //TODO: there is a bug here

                    if ($validateImage) {

                        $uploadImage = $user->uploadImage($_FILES['file']['tmp_name']);

                        if ($uploadImage) {
                            $finalUpload = $user->finalUpload($uploadImage, $_REQUEST['name'], $_REQUEST['design_id'], $_REQUEST['top'], $_REQUEST['left'], $_REQUEST['width'], $_REQUEST['border'], $_REQUEST['border_raduis_top_right'], $_REQUEST['border_raduis_top_left'], $_REQUEST['border_raduis_bottom_right'], $_REQUEST['border_raduis_bottom_left'], $_REQUEST['height'], $_REQUEST['border_color'], $_REQUEST['name_top'], $_REQUEST['name_left'], $_REQUEST['font_size'], $_REQUEST['font_weight'], $_REQUEST['font_color']);

                            if ($finalUpload) {
                                echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'Upload successful')));
                            } else {
                                echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'An error occurred')));
                                exit();
                            }
                        } else {
                            echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'An error occurred, could not upload image')));
                            exit();
                        } 
                    } else {
                        echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'Image file type not allowed')));
                        exit();
                    }
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