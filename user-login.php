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

use \Firebase\JWT\JWT;

require 'Firebase-JWT/src/JWT.php';

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $required_fields = array('email', 'password');
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
            // check if admin exists
            $userExist = $user->emailExist($_REQUEST['email']);
            if ($userExist) {
                $login = $user->loginUser($_REQUEST['email'], $_REQUEST['password']);
                $user_uuid = $user->getUserDetails($_REQUEST['email'])->fetch()['user_uuid'];
                if ($login) {
                    // jwt token
                    $secretKey = $_ENV['KEY'];
                    $issuer = $_ENV['DOMAIN'];
                    $audience = $_ENV['AUDIENCE'];
                    $issuedAt = time();
                    $notBefore = $issuedAt + 10;
                    $expirationTime = $issuedAt + 60 * 60 * 24; // set to 24 hr
                    // $expirationTime = $issuedAt + 60; // set to 1 minute
                    $payload = [
                        "iss" => $issuer,
                        "aud" => $audience,
                        "iat" => $issuedAt,
                        // "nbf" => $notBefore,
                        "exp" => $expirationTime,
                        "id" => $user_uuid
                    ];
                    
                    $jwt = JWT::encode($payload, $secretKey, "HS256");
                
                    echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'User login successful', 'token' => $jwt, 'email' => $_REQUEST['email'])));
                } else {
                    echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'Wrong password')));
                } 
            } else {
                echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'User doesn\'t exist')));
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