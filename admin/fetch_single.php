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
            foreach ($images as $image) {
                $newImageData = array(
                    "id" => $image["id"],
                    "file_name" => $image["file_name"],
                    "name" => $image["name"],
                    "design_id" => $image["design_id"],
                    "top" => $image["top"],
                    "left_side" => $image["left_side"],
                    "width" => $image["width"],
                    "border" => $image["border"],
                    "border_raduis_top_right" => $image["border_raduis_top_right"],
                    "border_raduis_top_left" => $image["border_raduis_top_left"],
                    "border_raduis_bottom_right" => $image["border_raduis_bottom_right"],
                    "border_raduis_bottom_left" => $image["border_raduis_bottom_left"],
                    "height" => $image["height"],
                    "border_color" => $image["border_color"],
                    "name_top" => $image["name_top"],
                    "name_left" => $image["name_left"],
                    "font_size" => $image["font_size"],
                    "font_weight" => $image["font_weight"],
                    "font_color" => $image["font_color"],
                    "date" => $image["date"]
                );
                $imageData[] = $newImageData;
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
