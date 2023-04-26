<?php

header("Access-Control-Allow-Origin: http://localhost:3000, https://rerhadp.vercel.app");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header('Content-Type: application/json');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

require_once "../classes/classes.php";

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $required_fields = array('name', 'design_id');
        $empty_fields = array();

        foreach ($required_fields as $field) {
            if (empty($_REQUEST[$field])) {
                $empty_fields[] = $field;
            }
        }
        if (!empty($empty_fields)) {
            echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'This fields ' . implode(', ', $empty_fields) . ' cannot be empty')));
            exit();
        } else {

            try {
                isset($_FILES['image']['name']) ? $image = $_FILES['image']['name'] : $image = null;

                // Check the file size
                if ($_FILES['image']['size'] > 10000000) {
                    echo json_encode(array('success' => false, 'code' => 400, 'data' => array("error" => "File size is too large. Maximum allowed size is 10 MB.")));
                    exit();
                }

                $validateImage = $user->validateImage($image); //TODO: there is a bug here

                if ($validateImage) {

                    $uploadImage = $user->uploadImage($_FILES['image']['tmp_name']);

                    if ($uploadImage) {
                        $user_uuid = $user->generate_uuid();
                        $finalUpload = $user->userUpload($user_uuid, $uploadImage, $_REQUEST['name'], $_REQUEST['design_id']);

                        if ($finalUpload) {
                            echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'Upload successful', 'user' => $user_uuid)));
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
