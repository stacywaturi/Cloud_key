<?php

//get database connection
include_once 'config/database.php';

//instantiate key object
include_once 'objects/key.php';

$database = new Database();
$db = $database->getConnection();

$request_method= $_SERVER["REQUEST_METHOD"];

switch ($request_method)
{
    case "POST":
        //Create Key
        sign();
        break;

    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
        break;

}


function sign()
{
    //echo "creating key";

    global $db;

    $key = new Key($db);

    //get posted data
    $data = json_decode(file_get_contents("php://input"));

    if(
        !empty($data->name) &&
        !empty($data->algorithm)&&
        !empty($data->hash)
    ){
        //set key property values
        $key->name = $data->name;
        $key->algorithm = $data->algorithm;
        $key->hash = $data->hash;


        //Create Key
        if($key->sign()){
            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array(
                "Status" => true,
                "Signature" => $key->signature
                ));

        }

        // if unable to create the product, tell the user
        else{
            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array(
                "Status" => false,
                "Message" => "Unable to sign. ".$key->keyVault_error
            ));

        }

    }

    // tell the user data is incomplete
    else{

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to create key. Data is incomplete."));
    }

}

