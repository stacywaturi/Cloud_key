<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/05
 * Time: 11:58
 */


require_once 'vendor/autoload.php';
use Azure\Keyvault\Certificate as KeyVaultCertificate;
use Azure\Authorisation\Token as AzureAuthorization;
use Azure\Config;

class Certificate
{

    // database connection and table name
    private $conn;
    private $table_name = '`certificates`';
    private $keyVaultCertificate;

    // DB object properties
    public $id;
    public $name;
    public $key_id;
    public $user_id;
    public $csr;
    public $certificate;
    public $previous_id;
    public $created_at;
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
    }


    function create()
    {




    }
}
