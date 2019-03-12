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
include_once 'key.php';
class Document
{

    private $document;
    public $name;
    private $table_name2 = '`certificates`';
    public $signature_setup_id;
    public $document_reference;
    public $certificate_name;
    public $document_signature;
    public $document_signature_base64;
    public $document_signer_error;
    public $document_digest="";
    private $document_hash;


    private $certificate_string;


    public function __construct($db)
    {
        $this->conn = $db;

        $this->document = new DocSigner([
            'baseUrl' => Config::$BASE_URL,
            ]
        );

    }

    function create()
    {







    }

    function get($id)
    {
        $getResponse = $this->document->getSignatureBlocks($id);
        var_dump($getResponse['data']['signature_blocks']);
//      var_dump($this->document->getSignatureBlocks($id));
    }

    function update()
    {

        $query = "SELECT * FROM $this->table_name2  WHERE `name`='$this->certificate_name' LIMIT 1";
        //prepare query
        $stmt = $this->conn->prepare($query);

        // echo $query;

        try{
            if( $stmt->execute()) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    // set values to object properties

                    $this->certificate_string = $row['certificate'];

//            var_dump($this->certificate_string);

                    $getDigestResponse = $this->document->getDigestValue($this->signature_setup_id, $this->document_reference, $this->certificate_string);
//            $this->document_hash = $getDigestResponse['data']['digest_value_base64'];


                    if ($getDigestResponse['responsecode'] == 200) {
                        $this->document_digest = $getDigestResponse['data']['digest_value_base64'];


                        $database = new Database();
                        $db = $database->getConnection();

                        $key = new Key($db);


                        //set key property values
                        $key->name = $this->certificate_name;
                        $key->algorithm = "RS256";
                        $key->hash = $this->document_digest;

                        //Create Key
                        if ($key->sign()) {
                            //Signature in base64 URL
                            $this->document_signature = $key->signature;
//               var_dump($this->document_signature);
                        } else {
                            $this->document_signer_error = $key->keyVault_error;
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


                        $this->document_signature_base64 = base64_encode(base64url_decode($this->document_signature));


                        $insertSignedValueResponse = $this->document->insertSignedValue($this->document_digest, $this->document_signature_base64);


                        if ($insertSignedValueResponse['data']['status']) {
                            return true;
                        }

                    }
                }
                else{
                    $this->document_signer_error = "Unable to retrieve certificate : ".$this->certificate_name;
                }
            }

            else{

                $this->document_signer_error = "Initialize signing document parameters failed!";
            }

            return false;

        } catch(PDOException $exception){
            var_dump( "Connection error: " . $exception->getMessage());
        }

        return false;

    }





}