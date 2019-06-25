<?php

//get database connection
include_once 'config/Database.php';

//instantiate key object
include_once 'objects/Key.php';

$database = new Database();
$db = $database->getConnection();

$request_method= $_SERVER["REQUEST_METHOD"];

/* API ROUTE DEFINITION ------ /sign
   --------------------------------------------------------------------------------
   SIGN WITH KEY:
   POST {baseURL}/sign?
   Request Body:
       {
           "id": "10792036075cc31001ee483",
            "algorithm":"RS256",
            "hash":"Y7zRoOtcqjw_Ik3TNDLPSlP4VjrYkCvIJdM8ckOalvA="

        }
    --------------------------------------------------------------------------------
   */

switch ($request_method)
{
    case "POST":
        sign();
        break;

    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
        break;

}


function sign()
{

    global $db;

    $key = new Key($db);

    //get posted data
    $data = json_decode(file_get_contents("php://input"));

    if(
        !empty($data->id) &&
        !empty($data->algorithm)&&
        !empty($data->hash)
    ){
        //set key property values
        $key->id = $data->id;
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

