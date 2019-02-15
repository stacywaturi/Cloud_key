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

}
function update_cert()
{

}

function delete_cert()
{

}