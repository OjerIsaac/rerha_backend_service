<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header('Content-Type: application/json');

require_once "./classes/classes.php";

use \Firebase\JWT\JWT;

require 'Firebase-JWT/src/JWT.php';

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
                    $secretKey = "secret";
                    $issuer = "your_domain";
                    $audience = "your_audience";
                    $issuedAt = time();
                    $notBefore = $issuedAt + 10;
                    $expirationTime = $issuedAt + 60 * 60 * 24;
                    $payload = [
                        "iss" => $issuer,
                        "aud" => $audience,
                        "iat" => $issuedAt,
                        "nbf" => $notBefore,
                        "exp" => $expirationTime,
                        "data" => [
                            "email" => $_POST['email']
                        ]
                    ];
                    $jwt = JWT::encode($payload, $secretKey, "HS256");
                
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