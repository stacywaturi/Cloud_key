<?php

//get database connection
include_once 'config/Database.php';

//instantiate key object
include_once 'objects/Key.php';


/* API ROUTE DEFINITION ------ /keys.

   --------------------------------------------------------------------------------
   CREATE KEY:
   POST {baseURL}/keys
   Request Body:
       {
           	"name":"key0403",
            "key_type":"RSA",
            "key_size": 2048

        }
    --------------------------------------------------------------------------------
   GET ALL KEYS:
   GET {baseURL}/keys?
   --------------------------------------------------------------------------------
   GET KEYS BY ID:
   GET {baseURL}/keys?id="13865453995c7cf57c02fe5"
    --------------------------------------------------------------------------------
    DELETE  KEY :
    DELETE {baseURL}/keys?id="13865453995c7cf57c02fe5"
    --------------------------------------------------------------------------------
   */

$database = new Database();
$db = $database->getConnection();

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        //Retrieve Keys
        if (!empty($_GET["id"])) {
            $id = strval($_GET["id"]);
            get($id);
        } else {
            //echo "get List";
            get_all_keys();
        }
        break;

    case "POST":
        //Create Key
        create_key();
        break;

    case "DELETE":
        if (!empty($_GET["id"])) {
            $id = strval($_GET["id"]);
            delete_key($id);
        } else
            echo json_encode(array("message" => "No id given"));


        break;

    case "PUT":
        update_key();
        break;

    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
        break;

}


function create_key()
{

    global $db;

    $key = new Key($db);

    //get posted data
    $data = json_decode(file_get_contents("php://input"));

    if (
        !empty($data->name) &&
        !empty($data->key_type) &&
        !empty($data->key_size) &&
        !empty($data->user_id)
    ) {
        //set key property values
        $key->name = $data->name;
        $key->key_type = $data->key_type;
        $key->key_size = $data->key_size;
        $key->user_id = $data->user_id;

        //Create Key
        if ($key->create()) {
            $key_arr = array(
                "key_id" => $key->id,
                "name" => $key->name,
                "public_key_n" => $key->public_key_n,
                "public_key_e" => $key->public_key_e,
                "use" => $key->usage,
                "key_operations" => $key->key_ops,
                "key_size" => $key->key_size,
                "key_type" => $key->key_type

            );
            // set response code - 201 created
            http_response_code(201);

            // tell the user

            echo json_encode(array("message" => "Key " . $key->name . " was created.", "data" => $key_arr));

        } // if unable to create the product, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to create key."));

        }

    } // tell the user data is incomplete
    else {

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to create key. Data is incomplete."));
    }

}


function get_all_keys()
{
    global $db;
    $key = new Key($db);
    $stmt = $key->get_all();
    $num = $stmt->rowCount();

    //Check if more than 0 records are found
    if ($num > 0) {
        $keys_arr = array();
        $keys_arr["keys"] = array();

        //retrieve table contents
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //Extract row
            extract($row);
            $key_item = array(
                "id" => $id,
                "name" => $name,
                "user_id" => $user_id,
                "use" => $use,
                "public_key" => $public_key_n,
                "vault_id" => $vault_id
            );

            array_push($keys_arr["keys"], $key_item);

        }

        http_response_code(200);
        echo json_encode((array)$keys_arr, JSON_PRETTY_PRINT);
    }

}

function get($id)
{
    global $db;
    $key = new Key($db);

    if ($id) {

        if ($key->get($id)) {
            $key_arr = array(
                "id" => $key->id,
                "name" => $key->name,
                "user_id" => $key->user_id,
                "use" => $key->usage,
                "public_key" => $key->public_key_n,
                "vault_id" => $key->vault_id,
                "key_version" => $key->key_version

            );
            // set response code - 200 OK
            http_response_code(200);
            // make it json format
            echo json_encode($key_arr);

        } else {
            // set response code - 400 bad request
            http_response_code(400);
            // tell the user
            echo json_encode(array("message" => "Unable to get key. Invalid id is given."));
        }
    }


}

function delete_key($id)
{
    global $db;

    $key = new Key($db);


    if ($id) {

        //Create Key
        if ($key->delete($id)) {
            // set response code - 201 created
            http_response_code(200);

            // tell the user
            echo json_encode(array("message" => "Key " . $key->name . " was deleted."));

        } // if unable to delete the product, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to delete key."));
        }

    } else {

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to delete key. No id is given."));
    }


}


function update_key()
{
    global $db;

    $key = new Key($db);

    //get posted data
    $data = json_decode(file_get_contents("php://input"));

    if (
        !empty($data->name) &&
        !empty($data->key_type) &&
        !empty($data->key_size)
    ) {
        //set key property values
        $key->name = $data->name;
        $key->key_type = $data->key_type;
        $key->key_size = $data->key_size;
        //Create Key
        if ($key->create()) {
            // set response code - 201 created
            http_response_code(201);

            // tell the user
            echo json_encode(array("message" => "Key " . $key->name . " was updated."));

        } // if unable to create the product, tell the user
        else {

            // set response code - 503 service unavailable
            http_response_code(503);

            // tell the user
            echo json_encode(array("message" => "Unable to update key."));

        }

    } // tell the user data is incomplete
    else {

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Unable to updated key. Data is incomplete."));
    }


}
