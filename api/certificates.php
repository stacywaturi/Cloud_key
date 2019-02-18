<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/15
 * Time: 11:12
 */
//get database connection
include_once 'config/database.php';

//instantiate key object
include_once 'objects/certificate.php';

$database = new Database();
$db = $database->getConnection();

$request_method= $_SERVER["REQUEST_METHOD"];

switch ($request_method)
{
    case "GET":
        //Retrieve Keys
        if (!empty($_GET["id"]))
        {
            $id = strval($_GET["id"]);
            get($id);
        }
        else {
            //echo "get List";
           get_all_certs();
        }
        break;

    case "POST":
        //Create Key
        generate_csr();
        break;

    case "DELETE":
        delete_cert();
        break;

    case "PUT":
        update_cert();
        break;

    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
        break;

}
function get($id)
{

}

function get_all_certs()
{

}
function generate_csr()
{

    global $db;

    $cert = new Certificate($db);
//    $key = new Key($db);


    $name = "";
    $email = "";
    $common_name = "";
    $organization = "";
    $organization_unit = "";
    $country = "";
    $state = "";
    $key_size = 2048;
    $key_type = "RSA";

    //get posted data
    $data = json_decode(file_get_contents("php://input"),true);


    if (!empty(  $data["name"]))
        $name = $data["name"];

    if (!empty(  $data["email"]))
        $email = $data["email"];

    if (!empty(  $data["common_name"]))
        $common_name = $data["common_name"];

    if (!empty( $data["organization"]))
        $organization = $data["organization"];

    if (!empty(  $data["organization_unit"]))
        $organization_unit = $data["organization_unit"];

    if (!empty( $data["country"]))
        $country = $data["country"];

    if (!empty(   $data["state"]))
        $state =   $data["state"];

    if (!empty(  $data["key_size"]))
        $key_size = $data["key_size"];

    if (!empty( $data["key_type"]))
        $key_type =  $data["key_type"];


    if(
        !empty($name) &&
        !empty($email)
    ) {
        //set certificate property values
        $cert->name = $name;
        $cert->email = $email;
        $cert->common_name = $common_name;
        $cert->organization = $organization;
        $cert->organization_unit = $organization_unit;
        $cert->country = $country;
        $cert->state = $state;

        $cert->key_size = $key_size;
        $cert->key_type = $key_type;


        //Create Key
        if ($cert->create()) {
            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array("message" => "Certificate " . $cert->name . " was created."));

        } // if unable to create the product, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to create certificate.  ".$cert->keyVault_error));

        }
    }
    // tell the user data is incomplete
    else{

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to create certificate. Data is incomplete. "));
    }

}
function update_cert()
{

}

function delete_cert()
{

}