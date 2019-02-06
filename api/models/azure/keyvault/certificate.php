<?php
/**
/*
* Class that handles certificate operations in the Key Vault
*
* @author stacy
* @date 2018-11-12
*
*/

namespace Azure\Keyvault;


class Certificate extends Vault
{
    public function __construct(array $keyVaultDetails)
    {
        parent::__construct($keyVaultDetails);
    }

    /* Creates a new certificate.
    If this is the first version, the certificate resource is created.
    This operation requires the certificates/create permission.
    --------------------------------------------------------------------------------
    POST {vaultBaseUrl}/certificates/{certificate-name}/create?api-version=2016-10-01
    Request Body:
    "policy":{
        "x509_props":{
            "subject": "CN=name123"
            },
        "issuer":{
            "name":"Self"/"Unknown"}
    --------------------------------------------------------------------------------
    */
    
    public function create(string $certName, string $subject, string $keyType, string $keySize)
    {
        if ($keyType == "" ) {
            $keyType = "RSA";

        }

        if($keySize == ""){
            $keySize = "2048";
        }

        $issuer = 'Unknown';
        $apiCall = "certificates/{$certName}/create?api-version=2016-10-01";
        $options = [
            'policy'   => [
                'key_props' =>[
                    'exportable' => false,
                    'kty' => $keyType,
                    'key_size' => $keySize,
                    'reuse_key' => false
                ],

                'x509_props' => [
                    'subject' => $subject,
                    'validity_months' => 24
                ],

                'issuer' => [
                    'name'=> $issuer
                 ]
            ]
        ];

        return $this->requestApi('POST', $apiCall, $options);
    }

    /* Gets the creation operation of a certificate.
    Gets the creation operation associated with a specified certificate.
    This operation requires the certificates/get permission.
    --------------------------------------------------------------------------------
    GET {vaultBaseUrl}/certificates/{certificate-name}/pending?api-version=2016-10-01
    --------------------------------------------------------------------------------

    */
    public function getCSR(string $certName)
    {
        $apiCall = "certificates/{$certName}/pending?api-version=2016-10-01";

         $response = $this->requestApi('GET', $apiCall);
         
         return $response;
         // var_dump($response);
         
    }

    /* Merges a certificate or a certificate chain with a key pair existing on the server.
   The MergeCertificate operation performs the merging of a certificate or certificate chain
   with a key pair currently available in the service.
   This operation requires the certificates/create permission.
   --------------------------------------------------------------------------------
   POST {vaultBaseUrl}/certificates/{certificate-name}/pending/merge?api-version=7.0

    Request Body:
    {
    "x5c": [ MIICxTCCAbi...EPAQj8= ]
    }
   --------------------------------------------------------------------------------
   */
    public function merge(string $certName, string $fileName)
    {
        $apiCall = "certificates/{$certName}/pending/merge?api-version=2016-10-01";
        $myfile = fopen("certs/".$fileName,"r") or die("Unable to open file");
        $cert = fread($myfile, filesize("certs/".$fileName));

        $cert = str_replace("-----BEGIN CERTIFICATE-----\r\n", "", $cert );
        $cert = str_replace("\r\n-----END CERTIFICATE-----", "", $cert );

        $options = [
            'x5c'   => [$cert]
            ];

        return $this->requestApi('POST', $apiCall, $options);

    }

    /* Gets information about a certificate.
   Gets information about a specific certificate.
   This operation requires the certificates/get permission.
   --------------------------------------------------------------------------------
   GET {vaultBaseUrl}/certificates/{certificate-name}/{certificate-version}?api-version=7.0
   --------------------------------------------------------------------------------
   */
    public function getCert(string $certName)
    {
        $apiCall = "certificates/{$certName}?api-version=7.0";

        $response = $this->requestApi('GET', $apiCall);

        return $response;
        //var_dump($response);


    }

    /* List certificates in a specified key vault
   The GetCertificates operation returns the set of certificates resources in the specified key vault.
   This operation requires the certificates/list permission.
  --------------------------------------------------------------------------------
  GET {vaultBaseUrl}/certificates?api-version=7.0
   **optional
   GET {vaultBaseUrl}/certificates?maxresults={maxresults}&includePending={includePending}&api-version=7.0
  --------------------------------------------------------------------------------
  */
    public function listCSRs()
    {
        $apiCall = "certificates?&includePending=true&api-version=7.0";

        $response = $this->requestApi('GET', $apiCall);

       // var_dump($response);
         return $response;
        // if ($response["responsecode"]==200) {
        //     return $response["responseMessage"];
        // }

        // else {
        //     var_dump($response["responseMessage"]);
        //     return -1;
        // }

    }

    /* List certificates in a specified key vault
    The GetCertificates operation returns the set of certificates resources in the specified key vault.
    This operation requires the certificates/list permission.
   --------------------------------------------------------------------------------
    GET {vaultBaseUrl}/certificates?api-version=7.0
    **optional
    GET {vaultBaseUrl}/certificates?maxresults={maxresults}&includePending={includePending}&api-version=7.0
   --------------------------------------------------------------------------------
   */
    public function listCerts()
    {
        $apiCall = "certificates?api-version=7.0";

        $response = $this->requestApi('GET', $apiCall);

       return $response;

    }
}