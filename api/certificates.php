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

    global $db;
    $cert = new Certificate($db);

    if($cert->get($id)){
        $key_arr = array(
            "name" => $cert->name,
            "csr" => $cert->csr,
            "cert" => $cert->certificate,
            "issuer" => $cert->issuer,
            "expiry" => $cert->expiry,
            "created_at" => $cert->created_at
            );

        // set response code - 200 OK
        http_response_code(200);

        // make it json format
        echo json_encode($key_arr);
    }

}

function get_all_certs()
{
    global $db;
    $cert = new Certificate($db);
    $stmt = $cert->get_all();
    $num = $stmt->rowCount();
    
    if($num>0){
        
        $cert_arr = array();
        $cert_arr["certificates"] = array();
        //retrieve table contents
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            //Extract row
            extract($row);
            $cert_item = array(
                "name" => $name,
                "csr" => $csr,
                "certificate" => $certificate,
                "issuer" => $issuer,
                "expiry" => $expiry,
                "created_at" => $created_at,

            );
            array_push($cert_arr["certificates"],$cert_item);


        }

        http_response_code(200);
        echo json_encode($cert_arr,JSON_PRETTY_PRINT);

    }



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

    global $db;

}

function delete_cert()
{
    global $db;

}