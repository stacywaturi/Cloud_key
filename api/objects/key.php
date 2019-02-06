<?php

require '../vendor/autoload.php';

use Azure\Keyvault\Key as KeyVaultKey;
use Azure\Authorisation\Token as AzureAuthorization;
use Azure\Config;


class Key{

    // database connection and table name
    private $conn;
    private $table_name = '`keys`';
    private $keyVaultKey;

    // object properties
    public $id;
    public $name;
    public $key_type;
    public $key_size;
    public $user_id;
    public $usage;
    public $public_key;
    public $vault_id;

    public function __construct($db)
    {
        $this->conn = $db;

        $this->keyVaultKey =  new KeyVaultKey(
           [
               'accessToken'  => AzureAuthorization::getKeyVaultToken(
                   [
                       'appTenantDomainName'   => Config::$APP_TENANT_ID ,
                       'clientId'              => Config::$CLIENT_ID,
                       'username'              => Config::$USERNAME,
                       'password'              => Config::$PASSWORD

                   ]
               ),
               'keyVaultName' => Config::$KEY_VAULT_NAME
           ]
       );
    }

    function create()
    {
        $createKeyResponse = $this->keyVaultKey->create($this->name, $this->key_type, $this->key_size);

        if ($createKeyResponse["responsecode"] == 200) {

            //Extract the modulus "n" of the public key
            $this->public_key = $createKeyResponse['data']['key']['n'];

            //Generate a unique random ID for the Keys ID column
            $this->id = uniqid(rand(),false);

            $this->user_id = "5115274945c501a7ba0f4e";
            //Usage is "General" for the "Create Key" request
            $this->usage = "General";

            $this->vault_id = 'ded1772b-feeb-41ac-bd65-95e150b46c79 
';
            /*Insert Key and attributes into Database
            */

            $query = "INSERT INTO ".$this->table_name."(`id`, `name`, `user_id`, `use`, `public_key`,`vault_id`) VALUES ('$this->id','$this->name','$this->user_id','$this->usage', '$this->public_key','$this->vault_id')";
            //Query to insert record
//            $query = "INSERT INTO
//                     " .$this->table_name ."
//                     SET
//                        id=:id, name=:name, user_id=:user_id, use=:use, public_key=:public_key, vault_id=:vault_id";


            //prepare query
            $stmt = $this->conn->prepare($query);



            try{
               if( $stmt->execute())
                 return true;

            } catch(PDOException $exception){
                echo "Connection error: " . $exception->getMessage();
            }

            return false;
        }

    }

    function get(){

    }



}