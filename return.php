<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header('Content-Type: application/json');

require_once "./classes/classes.php";
require_once "./vendor/autoload.php";

use \Firebase\JWT\JWT;

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Handle login
        if (empty($_POST)) {
            echo json_encode(array('success' => false, 'code' => 404, 'data' => array('message' => 'No data received')));
            exit();
        }
        elseif ((empty($_POST['email'])) || (empty($_POST['password'])) ) {
            echo json_encode(array('success' => false, 'code' => 404, 'data' => array('message' => 'No field can be left empty')));
        }
        else {
            // check if admin exists
            $adminExist = $user->adminExist($_POST['email']);
            if ($adminExist) {
                $login = $user->loginUser($_POST['email'], $_POST['password']);
                if ($login) {
                    // jwt token
                    
                
                    echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'User login successful', 'token' => $jwt, 'email' => $_POST['email'])));
                } else {
                    echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'Wrong password')));
                } 
            } else {
                echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'Invalid admin email')));
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