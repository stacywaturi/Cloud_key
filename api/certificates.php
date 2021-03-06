<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/15
 * Time: 11:12
 */
//get database connection
include_once 'config/Database.php';

//instantiate key object
include_once 'objects/Certificate.php';

/* API ROUTE DEFINITION ------ /certificates.

   --------------------------------------------------------------------------------
   CREATE CSR:
   POST {baseURL}/certificates
   Request Body:
       {
            "name": "Cert0403" ,
            "email": "cert0403@example.com",
            "common_name": "cert0403.com",
            "organization": "Cert0403",
            "organization_unit": "Unit Cert0403",
            "country": "ZA",
            "state": "Gauteng",
            "key_size": 4096,
            "key_type": "RSA",
	        "user_id" : "12343"
        }
    --------------------------------------------------------------------------------
   GET ALL CERTIFICATES:
   GET {baseURL}/certificates?

    --------------------------------------------------------------------------------
   GET  CERTIFICATE BY ID:
   GET {baseURL}/certificates??id="13865453995c7cf57c02fe5"

    --------------------------------------------------------------------------------
    MERGE  CERTIFICATE :
    PUT {baseURL}/certificates?
    {
        "name": "Cert0403",
        "certificate": "MIIFczCCA1sCAhAjMA0GCSqGSIb3DQEBCwUA.."
    }

    --------------------------------------------------------------------------------
   */

//Initialize DB connection
$database = new Database();
$db = $database->getConnection();
//Get Request
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {


    case "GET":
        //Retrieve Keys
        if (!empty($_GET["id"])) {
            $id = strval($_GET["id"]);
            get($id);
        } else {
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

    if ($cert->get($id)) {
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

    if ($num > 0) {

        $cert_arr = array();
        $cert_arr["certificates"] = array();
        //retrieve table contents
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
            array_push($cert_arr["certificates"], $cert_item);


        }

        http_response_code(200);
        echo json_encode($cert_arr, JSON_PRETTY_PRINT);

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
    $user_id = "";
    //get posted data
    $data = json_decode(file_get_contents("php://input"), true);


    if (!empty($data["name"]))
        $name = $data["name"];

    if (!empty($data["email"]))
        $email = $data["email"];

    if (!empty($data["common_name"]))
        $common_name = $data["common_name"];

    if (!empty($data["organization"]))
        $organization = $data["organization"];

    if (!empty($data["organization_unit"]))
        $organization_unit = $data["organization_unit"];

    if (!empty($data["country"]))
        $country = $data["country"];

    if (!empty($data["state"]))
        $state = $data["state"];

    if (!empty($data["key_size"]))
        $key_size = $data["key_size"];

    if (!empty($data["key_type"]))
        $key_type = $data["key_type"];

    if (!empty($data["user_id"]))
        $user_id = $data["user_id"];

    if (
        !empty($name) &&
        !empty($email) &&
        !empty($user_id)

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

        $cert->user_id = $user_id;

        //Create Key
        if ($cert->create()) {

            $cert_arr = array(
                "key_id" => $cert->key_id,
                "key" => $cert->public_key,
                "cert_id" => $cert->id,
                "name" => $cert->name,
                "csr" => $cert->csr,
                "cert" => $cert->certificate,
                "issuer" => $cert->issuer,
                "expiry" => $cert->expiry,
                "created_at" => $cert->created_at
            );

            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array("message" => "Certificate " . $cert->name . " was created.", "data" => $cert_arr));

        } // if unable to create the product, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to create certificate.  " . $cert->keyVault_error));

        }
    }
    // tell the user data is incomplete
    //tell
    else {

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to create certificate. Data is incomplete. "));
    }

}

function update_cert()
{

    global $db;
    $cert = new Certificate($db);

    $name = "";
    $certificate = "";

    //get posted data
    $data = json_decode(file_get_contents("php://input"), true);


    if (!empty($data["name"]))
        $name = $data["name"];

    if (!empty($data["certificate"]))
        $certificate = $data["certificate"];


    if (
        !empty($name) &&
        !empty($certificate)
    ) {
        //set certificate property values
        $cert->name = $name;
        $cert->certificate = $certificate;


        //Create Key
        if ($cert->update()) {
            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array("message" => "Certificate " . $cert->name . " was updated."));

        } // if unable to create the product, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to update certificate.  " . $cert->keyVault_error));

        }
    } // tell the user data is incomplete
    else {

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to create certificate. Data is incomplete. "));
    }

}

function delete_cert()
{
    global $db;

    $cert = new Certificate($db);

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->name)) {
        //set certificate property values
        $cert->name = $data->name;

        //Delete cert
        if ($cert->delete()) {
            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array("message" => "Certificate " . $cert->name . " was deleted."));
        } // if unable to delete the cert, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to delete certificate."));

        }
    }

}