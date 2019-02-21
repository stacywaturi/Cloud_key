<?php


require_once 'vendor/autoload.php';

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
    public $algorithm;
    public $hash;
    public $signature;
    public $keyVault_error;

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

            $this->vault_id = '1320b3cb-860b-4ea4-8a60-a01e138834ff';
            /*Insert Key and attributes into Database
            */

            $query = "INSERT INTO 
                     ".$this->table_name."
                     (`id`, `name`, `user_id`, `use`, `public_key`,`vault_id`) 
                     VALUES 
                     ('$this->id','$this->name','$this->user_id','$this->usage', '$this->public_key','$this->vault_id')";
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

        else{
            return false;
        }

    }

    function get_all()
    {

        $query = "SELECT * FROM ".$this->table_name;
        //prepare query
        $stmt = $this->conn->prepare($query);


        try{
            if( $stmt->execute())
                return $stmt;

        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return false;

    }

    function get($id="")
    {
        $query = "SELECT * FROM ".$this->table_name. "WHERE `id`=".$id." LIMIT 1";
        //prepare query
        $stmt = $this->conn->prepare($query);

       // echo $query;

        try{
            if( $stmt->execute())
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // set values to object properties
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->usage = $row['use'];
                $this->user_id = $row['user_id'];
                $this->public_key = $row['public_key'];
                $this->vault_id = $row['vault_id'];

                return true;

        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return false;

    }

    function delete()
    {
        $query = "DELETE FROM ".$this->table_name. "WHERE `name`=".$this->name;

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

    function sign()
    {
        $keyResponse = $this->keyVaultKey->get($this->name);

        if ($keyResponse["responsecode"] == 200){

            $keyID =  $keyResponse['data']['key']['kid'];
            $signResponse = $this->keyVaultKey->sign($keyID, $this->algorithm,$this->hash);

            if ($signResponse["responsecode"] == 200) {
                $signatureValue = $signResponse['data']['value'];
                $this->signature = $signatureValue;
                return true;
            }

            else {
                $this->keyVault_error = $signResponse["responseMessage"]["message"];
                return false;
            }

        }

        else{
            $this->keyVault_error = $keyResponse["responseMessage"]["message"];
            return false;

        }



    }




}