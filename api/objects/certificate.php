<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/05
 * Time: 11:58
 */


require_once 'vendor/autoload.php';
use Azure\Keyvault\Certificate as KeyVaultCertificate;
use Azure\Keyvault\Key as KeyVaultKey;
use Azure\Authorisation\Token as AzureAuthorization;
use Azure\Config;

class Certificate
{

    // database connection and table name
    private $conn;
    private $table_name = '`keys`';
    private $table_name2 = '`certificates`';
    private $keyVaultCertificate;
    private $keyVaultKey;

    // DB object properties
    public $id;
    public $name;
    public $key_id;
    public $user_id;
    public $vault_id;
    public $csr;
    public $certificate;
    public $previous_id;
    public $created_at;
    public $updated_at;
    public $expiry;

    public $serial_number;
    public $issuer;
    public $keyVault_error;

    //Cert subject properties
    public $common_name;
    public $email;
    public $organization;
    public $organization_unit;
    public $state;
    public $country;

    public $key_size;
    public $key_type;
    public $public_key;


    public function __construct($db)
    {
        $this->conn = $db;

        $this->keyVaultCertificate = new KeyVaultCertificate(
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

        $this->keyVaultKey = new KeyVaultKey(
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

        //Certificate attributes
        $subject_cn = 'CN='.$this->common_name;
        $subject_e       = 'E='. $this->email;
        $subject_o         = 'O='. $this->organization;
        $subject_ou    = 'OU='.$this->organization_unit;
        $subject_s       = 'S='. $this->state;
        $subject_c   = 'C='. $this->country;

        $subject = $subject_cn.','.$subject_e.','.$subject_o.','.$subject_ou.','.$subject_c.','.$subject_s;

        $createCertResponse = $this->keyVaultCertificate->create($this->name,$subject,$this->key_type,$this->key_size);
//        $createKeyResponse = $this->keyVaultKey->create($this->name, $this->key_type, $this->key_size);

        if ($createCertResponse["responsecode"] == 202) {

            //Extract the CSR
            $this->csr = $createCertResponse['data']['csr'];

            //Get created Key
            $getKeyResponse = $this->keyVaultKey->get($this->name);

            if($getKeyResponse["responsecode"] == 200) {
                $this->public_key = $getKeyResponse['data']['key']['n'];
            }

            else{
                $this->keyVault_error = $getKeyResponse["responseMessage"]["message"];
                $this->public_key="";
            }

            //Generate a unique random ID for the Keys ID column
            $this->key_id = uniqid(rand(),false);

            $this->user_id = "5115274945c501a7ba0f4e";
            //Usage is "General" for the "Create Key" request
            $this->usage = "Certificate";

            $this->vault_id = '1320b3cb-860b-4ea4-8a60-a01e138834ff';
            $this->id = uniqid(rand(),false);
            /*Insert Key and attributes into Database
            */

            $query = "INSERT INTO
                     ".$this->table_name."
                     (`id`, `name`, `user_id`, `use`, `public_key`,`vault_id`)
                     VALUES
                     ('$this->key_id','$this->name','$this->user_id','$this->usage', '$this->public_key','$this->vault_id')";
            //Query to insert record
//            $query = "INSERT INTO
//                     " .$this->table_name ."
//                     SET
//                        id=:id, name=:name, user_id=:user_id, use=:use, public_key=:public_key, vault_id=:vault_id";


            //prepare query
            $stmt = $this->conn->prepare($query);



            try{
                if( $stmt->execute()) {

                    $query2 = "INSERT INTO 
                      " . $this->table_name2 . "
                      (`id`, `name`,`key_id`, `user_id`, `csr`)
                      VALUES
                      ('$this->id','$this->name','$this->key_id','$this->user_id','$this->csr')";

                    $stmt2 = $this->conn->prepare($query2);

                    try {
                        if ($stmt2->execute()) {
                            return true;
                        }


                    } catch (Exception $e) {
                        echo "Exception->  ";
                        var_dump($e->getMessage());

                    }


                }


            } catch(PDOException $exception){
                echo "Connection error: " . $exception->getMessage();
            }

            return false;
        }

        else{
            $this->keyVault_error = $createCertResponse["responseMessage"]["message"];
            return false;
        }

    }
    function get($id="")
    {
        $query = "SELECT * FROM ".$this->table_name2. "WHERE `id`=".$id." LIMIT 1";
        //prepare query
        $stmt = $this->conn->prepare($query);

        // echo $query;

        try{
            if( $stmt->execute())
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // set values to object properties
            $this->name = $row['name'];
            $this->csr = $row['csr'];
            $this->certificate = $row['certificate'];
            $this->issuer = $row['issuer'];
            $this->expiry = $row['expiry'];
            $this->created_at = $row['created_at'];



            return true;

        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return false;

    }

    function get_all()
    {

        $query = "SELECT * FROM ".$this->table_name2;
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


    function update(){

        //Append PEM attributes for the cert as openssl only accepts mixed certificate types
        $cert = "-----BEGIN CERTIFICATE-----\r\n". $this->certificate. "\r\n-----END CERTIFICATE-----";
        //Extract the public key from the certificate
        if($this->extract_pub_key($cert)){
//            $this->public_key = "r5c1scoD2FKeJXohFEQasfQeWTbD5UTqcRk3NdQXb0utJqZlvz9hjsnI3Y04HnDE9VEP56CWP3fOFCPCxCKnhu5QqcYalAJZMUVtyfShhJXgDVrkn9RGYQlxHYowb_Ic-E2_qSQ-vXBtFSez9R2aRNRGIm643Mgir_scOAvIbdUg-KJLyg6RM3b0ghxfyPHit-1MNZav_7ZACgdx5jYaigcgnOOont1Vdf_5EUcUNMGiFvfVE3hJ2SYOSy6H3oPj4T76ByYVhYNitYBM_LNNh0zyGS5DtxKIpnYqaFTC3pqvFiAuT12J_NbbSJh0JWyhuRnEUJkTf4-C6Quabdy2Fw";
            //Search for this public key in DB

            $query = "SELECT * FROM $this->table_name  WHERE `public_key`= '$this->public_key'";
            //prepare query
            $stmt = $this->conn->prepare($query);
            // echo $query;

            try{
                if( $stmt->execute())
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
//                    var_dump($row);
                    if($row['name'] == $this->name) {
                        $query2 = "UPDATE $this->table_name2 SET `certificate` = '$this->certificate' WHERE `name`= '$this->name'";
//
                        $stmt2 = $this->conn->prepare($query2);
//
                        try {
                            if ($stmt2->execute()) {
                                return true;
                            }
                        }
                        catch(PDOException $exception){
                                echo "Connection error: " . $exception->getMessage();
                            }
                    }
                    else
                        $this->keyVault_error = "Error in updating Certificate to Database";

            } catch(PDOException $exception){
                echo "Connection error: " . $exception->getMessage();
            }

            return false;
        }

        else{
            $this->keyVault_error = "Property x5c has invalid value. X5C must have at least one valid item";
        }

    }

    function extract_pub_key($cert){

        function base64_url_encode( $data ) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        $resource = openssl_pkey_get_public($cert);

        if($resource) {
            $array =openssl_pkey_get_details($resource);
            $key_base64url = array_map("base64_url_encode", $array["rsa"]);
            $this->public_key = $key_base64url["n"];

            return true;
        }

        return false;

    }

    function delete()
    {
        $query = "DELETE FROM $this->table_name2 WHERE `name`='$this->name'";
//        $query = "DELETE FROM ".$this->table_name2. "WHERE `name`=".$this->name;

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

