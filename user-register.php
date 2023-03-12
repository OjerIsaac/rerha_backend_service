<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Origin: https://rerhadp.vercel.app");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header('Content-Type: application/json');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

// Load DotEnvironment Class
require_once './classes/env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

require_once "./classes/classes.php";

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $required_fields = array('name', 'email', 'password', 'confirm_password');
        $empty_fields = array();

        foreach ($required_fields as $field) {
            if (empty($_REQUEST[$field])) {
                $empty_fields[] = $field;
            }
        }
        if (!empty($empty_fields)) {
            echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'This fields '.implode(', ', $empty_fields).' cannot be empty'  )));
            exit();
        } elseif ($_REQUEST['password'] != $_REQUEST['confirm_password']) {
            echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'Passwords don\'t match'  )));
        } else {
            if (filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) { //TODO: still bug here
                $emailExist = $user->emailExist($_REQUEST['email']);
                if (!$emailExist) {
                    $user_uuid = $user->generate_uuid();
                    $user->registerUser($user_uuid, $_REQUEST['name'], $_REQUEST['email'], $_REQUEST['password']);
                    
                    echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'Registration successful')));
                } else {
                    echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'This email already exist')));
                }
            } else {
                echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'This is not a valid email')));
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