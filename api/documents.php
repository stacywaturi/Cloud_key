<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/07
 * Time: 14:32
 */

//get database connection
include_once 'config/database.php';

//instantiate document object
include_once 'objects/document.php';



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

function get_all_docs()
{

}

function get($id)
{

    global $db;

    $doc = new Document($db);
    $doc->get($id);


}

function create_doc()
{

}

function delete_doc()
{

}

function update_doc()
{

    //echo "creating key";

    global $db;

    $doc = new Document($db);

    //get posted data
    $data = json_decode(file_get_contents("php://input"));

    if(
        !empty($data->signature_setup_id)&&
        !empty($data->document_reference)&&
        !empty($data->certificate_name)
    ){
        //set key property values
        $doc->signature_setup_id = $data->signature_setup_id;
        $doc->document_reference = $data->document_reference;
        $doc->certificate_name = $data->certificate_name;
        //Create Key
        if($doc->update()){
            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array("message" => "Document succesfully signed."));

        }

        // if unable to create the product, tell the user
        else{
            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to sign document. ".$doc->document_signer_error));

        }
    }

    // tell the user data is incomplete
    else{

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to updated key. Data is incomplete."));
    }



}
