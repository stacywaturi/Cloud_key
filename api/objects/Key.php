<?php

require_once 'vendor/autoload.php';

use Azure\KeyVault\Key as KeyVaultKey;
use Azure\Authorisation\Token as AzureAuthorization;
use Azure\Config;

class Key {

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
    public $public_key_n;
    public $public_key_e;
    public $key_ops;
    public $vault_id;
    public $algorithm;
    public $hash;
    public $signature;
    public $key_version;
    public $keyVault_error;

    /*Key Vault Authentication -- getting Key Vault Token
      -------------------------------------------------------------------------------
    */
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

    /*Create Key Vault Key and save key instance and properties in Cloud Key Database
    -------------------------------------------------------------------------------
  */
    function create()
    {
        $createKeyResponse = $this->keyVaultKey->create($this->name, $this->key_type, $this->key_size);

        if ($createKeyResponse["responsecode"] == 200) {

            //Extract the modulus "n" amd exponent "e" of the public key
            $this->public_key_n = $createKeyResponse['data']['key']['n'];
            $this->public_key_e = $createKeyResponse['data']['key']['e'];

            //Extract the possible key operations as an array
            $this->key_ops = json_encode( $createKeyResponse['data']['key']['key_ops']);

            //Extract the key key reference in Azure
            $this->key_version = $createKeyResponse['data']['key']['kid'];

            //Generate a unique random ID for the Keys ID column
            $this->id = uniqid(rand(),false);

            //Usage is "General" for the "Create Key" request
            $this->usage = "General";

            $this->vault_id = '1320b3cb-860b-4ea4-8a60-a01e138834ff';

            /*Insert Key and attributes into Database
            */
            $query = "INSERT INTO 
                     ".$this->table_name."
                     (`id`, `name`, `user_id`, `use`, `public_key_n`,`vault_id`,`public_key_e`, `key_ops`,`key_type`, `key_size`,`key_version`) 
                     VALUES 
                     ('$this->id','$this->name','$this->user_id','$this->usage', '$this->public_key_n','$this->vault_id','$this->public_key_e','$this->key_ops','$this->key_type','$this->key_size','$this->key_version')";


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

    /* Get all key instances from the key vault
   -------------------------------------------------------------------------------
 */
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

    /* Get particular key instance by its Database ID from the key vault
  -------------------------------------------------------------------------------
*/

     function get( $id = "")
    {

        $query = "SELECT * FROM ".$this->table_name. "WHERE `id`=".$id;
        //prepare query
        $stmt = $this->conn->prepare($query);

       // echo $query;

        try{
            if( $stmt->execute())
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(!$row)
                    return false;
                // set values to object properties
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->usage = $row['use'];
                $this->user_id = $row['user_id'];
                $this->public_key_n = $row['public_key_n'];
                $this->vault_id = $row['vault_id'];
                $this->key_version = $row['key_version'];

                return true;

        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return false;

    }

    /* Delete particular key instance by its Database ID from the API
 -------------------------------------------------------------------------------
*/

    function delete($id)
    {

       if($this->get($id)){

           $query = "DELETE FROM ".$this->table_name."WHERE `id`=".$id;

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

    /* Sign with particular key in DB
    -------------------------------------------------------------------------------
    */

    function sign()
    {

        //Get particular key

        $keyResponse = $this->get('"'.$this->id.'"');


        if ($keyResponse){
            //Get the key version reference on Azure
            $keyID =  $this->key_version;

            //Sign with given key
            $signResponse = $this->keyVaultKey->sign($keyID, $this->algorithm,$this->hash);


            //If success, return signature
            if ($signResponse["responsecode"] == 200) {
                $signatureValue = $signResponse['data']['value'];
                $this->signature = $signatureValue;
                return true;
            }

            //Return signing error from Azure
            else {
                $this->keyVault_error = $signResponse["responseMessage"]["message"];
                return false;
            }

        }

        //Return key error from Azure
        else{
            $this->keyVault_error = $keyResponse["responseMessage"]["message"];
            return false;

        }

    }

}