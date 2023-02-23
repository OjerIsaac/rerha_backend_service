<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// Load DotEnvironment Class
require_once '../classes/env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

require_once "../classes/classes.php";

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
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
