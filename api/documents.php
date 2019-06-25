<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/07
 * Time: 14:32
 */

//get database connection
include_once 'config/Database.php';

//instantiate document object
include_once 'objects/Document.php';


/* API ROUTE DEFINITION ------ /documents.

    --------------------------------------------------------------------------------
   GET DOCUMENT BY ID:
   GET {baseURL}/certificates??id=C:\\Users\\Stacy\\Desktop\\Document signer\\Doc25062.docx

    --------------------------------------------------------------------------------
    UPDATE/SIGN  CERTIFICATE :
    PUT {baseURL}/documents?
    {
      	"signature_setup_id": "{52EE61CC-11B5-4855-A394-BC69163FFC0D}",
        "document_reference": "C:\\Users\\Stacy\\Desktop\\Document signer\\Doc25062.docx",
        "certificate_string": "-----BEGIN CERTIFICATE-----\r\nMIIELjCCAhYCAhA..
                                ...oMA0GCSq\njD4G72\r\n
                               -----END CERTIFICATE-----\r\n",
        "key_id": "15327034615d0ca1ba0096b"
        **Optional**
        "signature_tex": "This is a signature",
        "signature_image_reference": "C:\\Users\\Stacy\\Desktop\\Document signer\\signatureimage.png",
        "digest_algorithm": "SHA256",
        "service_id": "localFileSystem",
    }

    --------------------------------------------------------------------------------
   */

$database = new Database();
$db = $database->getConnection();

$request_method= $_SERVER["REQUEST_METHOD"];

switch ($request_method)
{
    case "GET":
        //Retrieve Keys

        if (!empty($_GET["id"]))
        {
            $id = $_GET["id"];
            get($id);
        }
        else {
            //echo "get List";
            get_all_docs();
        }
        break;

    case "POST":
        //Create/Upload Document
       // create_doc();
        if (!empty($_GET["id"]))
        {
            $id = $_GET["id"];
            get($id);
        }
        break;

    case "DELETE":
        delete_doc();
        break;

    case "PUT":
        //sign document with certificate
        update_doc();
        break;

    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
        break;

}

function get($id)
{

    global $db;

    $doc = new Document($db);

    if ($doc->get($id)) {
        $doc_arr = $doc->signature_blocks;

        // set response code - 200 OK
        http_response_code(200);

        // make it json format
        echo json_encode($doc_arr, JSON_PRETTY_PRINT);
    }


}

function update_doc()
{
    global $db;

    $doc = new Document($db);

    $signature_setup_id = "";
    $document_reference = "";
    $certificate_string = "";
    $key_id = "";

    //Defaults for optional
    $signature_tex =  "This is a signature";
    $signature_image_reference = null;
    $digest_algorithm=  "SHA256";
    $service_id =  "localFileSystem";

    //get posted data
    $data = json_decode(file_get_contents("php://input"),true);

    if (!empty($data["signature_setup_id"]))
        $signature_setup_id = $data["signature_setup_id"];

    if (!empty($data["document_reference"]))
        $document_reference= $data["document_reference"];

    if (!empty($data["certificate_string"]))
        $certificate_string = $data["certificate_string"];

    if (!empty($data["key_id"]))
        $key_id = $data["key_id"];

    if (!empty($data["signature_tex"]))
        $signature_tex = $data["signature_tex"];

    if (!empty($data["signature_image_reference"]))
        $signature_image_reference = $data["signature_image_reference"];

    if (!empty($data["digest_algorithm"]))
        $digest_algorithm = $data["digest_algorithm"];


    if (!empty($data["service_id"]))
        $service_id = $data["service_id"];


    if(
        !empty($signature_setup_id)&&
        !empty($document_reference)&&
        !empty($certificate_string)&&
        !empty($key_id )
    ){
        //set key property values
        $doc->signature_setup_id = $signature_setup_id;
        $doc->document_reference = strval($document_reference);
        $doc->certificate_string = $certificate_string;
        $doc->key_id             = $key_id;

        $doc->optional_params = array(
            "signature_tex" => $signature_tex,
            "signature_image_reference" => $signature_image_reference,
            "service_id" => $service_id,
            "digest_algorithm" => $digest_algorithm
        );

        //Create Key
        if($doc->update()){
            // set response code - 201 created
            http_response_code(200);

            // tell the user
            echo json_encode(array("message" => "Document successfully signed."),JSON_PRETTY_PRINT);

        }

        // if unable to create the product, tell the user
        else{
            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to sign document. ".$doc->document_signer_error),JSON_PRETTY_PRINT);

        }
    }

    // tell the user data is incomplete
    else{

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to updated key. Data is incomplete."),JSON_PRETTY_PRINT);
    }

}


function create_doc()
{

}

function delete_doc()
{

}

function get_all_docs()
{

}