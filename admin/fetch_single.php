<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

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
    case 'GET':
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
            }else {
                $id = $_GET['design_id'];
                // fetch single files
                $images = $user->fetchOneImage($id);

                if ($images) {
                    $imageData = [];
                    foreach ($images as $image) {
                        $imageData[] = $image;
                    }
                    echo json_encode(array('success' => true, 'code' => 200, 'data' => array('message' => 'Image fetched successfully', 'image' => $imageData)));
                } else {
                    echo json_encode(array('success' => false, 'code' => 400, 'data' => array('message' => 'No image found')));
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(array('success' => false, 'code' => 401, 'data' => array('message' => 'Invalid Token')));
            exit();
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
