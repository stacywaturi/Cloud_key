<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/07
 * Time: 14:33
 */

require_once 'vendor/autoload.php';
use DocSigner\CloudKeyDocSigner as DocSigner;
use DocSigner\Config as Config;
include_once 'Key.php';
class Document
{

    private $document;
    public $name;
    private $table_name2 = '`certificates`';
    private $table_name = '`keys`';
    public $signature_setup_id;
    public $document_reference;
    public $signature_blocks;
    public $certificate_name;
    public $key_id;
    public $optional_params;
    public $certificate_string;
    public $document_signature;
    public $document_signature_base64;
    public $document_signer_error;
    public $document_digest="";
    private $document_hash;


   // private $certificate_string;


    /*Document Signer -- set base URL for the Doc Signer API
    -------------------------------------------------------------------------------
  */
    public function __construct($db)
    {
        $this->conn = $db;

        $this->document = new DocSigner([
            'baseUrl' => Config::$BASE_URL,
            ]
        );

    }

    /* Get particular document's signature blocks
     -------------------------------------------------------------------------------
    */
    function get($id)
    {
        //Call to get documents signature blocks
        $getResponse = $this->document->getSignatureBlocks($id);

        if($getResponse['responsecode']==200) {
            $this->signature_blocks = $getResponse['data']['signature_blocks'];
            return true;
        }

        return false;

    }

    /* Update signature to document
     -------------------------------------------------------------------------------
    */
    function update()
    {

        //Get certificate from DB
        $query = "SELECT * FROM $this->table_name  WHERE `id`='$this->key_id' LIMIT 1";
        //prepare query
        $stmt = $this->conn->prepare($query);


        try{
            if( $stmt->execute()) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    //Remove -----BEGIN and -----END tags
                    $this->certificate_string = $this->removeTags($this->certificate_string);

                    //Get Digest Value of Document
                    $getDigestResponse = $this->document->getDigestValue($this->signature_setup_id, $this->document_reference, $this->certificate_string, $this->optional_params);
//                    var_dump($getDigestResponse) ;

                    //If Digest is successfully created..
                    if ($getDigestResponse['responsecode'] == 200) {

                        //Get Digest Value from response
                        $this->document_digest = $getDigestResponse['data']['digest_value_base64'];

                        //Get key that corresponds to certificate string fromDB
                        $database = new Database();
                        $db = $database->getConnection();

                        //Initialize key instance for signing
                        $key = new Key($db);

                        //Set key property values
                        $key->id = $this->key_id;
                        $key->algorithm = "RS256";
                        $key->hash = $this->document_digest;

                        //Sign with key
                        if ($key->sign()) {
                            //Signature in base64 URL
                            $this->document_signature = $key->signature;

                            $this->document_signature_base64 = base64_encode($this->base64url_decode($this->document_signature));

                            //Insert the Signed value to document
                            $insertSignedValueResponse = $this->document->insertSignedValue($this->document_digest, $this->document_signature_base64);
//                           var_dump($insertSignedValueResponse);

                            if ($insertSignedValueResponse['data']['status']) {
                                return true;
                            }

                            else{
                                $this->document_signer_error = "The process cannot access the file because it is being used by another process";
                            }

                        } else {
                            $this->document_signer_error = $key->keyVault_error;
                        }



                    }
                    else{
                        $this->document_signer_error = "Initial signing document parameters failed!";
                    }
                }
                else{
                    $this->document_signer_error = "Unable to retrieve key : ";
                }
            }

            else{

                $this->document_signer_error =  "Internal Server Error";
            }

            return false;

        } catch(PDOException $exception){
            var_dump( "Connection error: " . $exception->getMessage());
        }

        return false;

    }


    //Convert to base64 for Document signer
    function base64_url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    function removeTags($cert){
        $removed_begin = str_replace("-----BEGIN CERTIFICATE-----\r\n","",$cert);
        $removed_end = str_replace("\r\n-----END CERTIFICATE-----\r\n","",$removed_begin);

        return $removed_end;
    }


}