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
    case 'GET':
        $required_fields = array('design', 'user');
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
                // fetch single files
                $designs = $user->fetchOneImage($_REQUEST['design'])->fetch(PDO::FETCH_ASSOC);
                $user_upload = $user->fetchUserImage($_REQUEST['user'])->fetch(PDO::FETCH_ASSOC);

                if ($designs && $user_upload) {    
                    echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'Image fetched successfully', 'user' => array('name' => $user_upload['name'], 'user_image' => $user_upload['image'], 'design' => $designs['file_name']))));
                } else {
                    echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'No image found')));
                    exit();
                }
            } catch (\Throwable $e) {
                error_log($e->getMessage());
                echo json_encode(array('success' => false, 'code' => 500, 'data' => array('message' => 'ID tampered with')));
                exit();
            }
        }
        break;
    case 'POST':
        // Handle other operations
        echo json_encode(array('success' => false, 'code' => 505, 'data' => array('message' => 'Method not allowed')));
        break;
    default:
        // Handle invalid request methods
        echo json_encode(array('success' => false, 'code' => 505, 'data' => array('message' => 'Method not allowed')));
        break;
}
