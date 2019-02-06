<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/05
 * Time: 12:22
 */
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//get database connection
include_once '../config/database.php';

//instantiate key object
include_once '../objects/key.php';

$database = new Database();
$db = $database->getConnection();

$key = new Key($db);

//get posted data
$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->name) &&
    !empty($data->key_type)&&
    !empty($data->key_size)
){
    //set key property values
    $key->name = $data->name;
    $key->key_type = $data->key_type;
    $key->key_size = $data->key_size;

    //Create Key
    if($key->create()){
        // set response code - 201 created
        http_response_code(201);

        // tell the user
        echo json_encode(array("message" => "Key ".$key->name ." was created."));

    }

    // if unable to create the product, tell the user
    else{

        // set response code - 503 service unavailable
        http_response_code(503);

        // tell the user
        echo json_encode(array("message" => "Unable to create key."));

    }

}

// tell the user data is incomplete
else{

    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("message" => "Unable to create key. Data is incomplete."));
}