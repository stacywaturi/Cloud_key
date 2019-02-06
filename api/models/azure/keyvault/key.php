<?php

/*
* Class that handles key operations in the Key Vault
*
*
* @author stacy
* @date 2018-11-12
*
*/
namespace Azure\Keyvault;

class Key extends Vault
{
    public function __construct(array $keyVaultDetails)
    {
        parent::__construct($keyVaultDetails);
    }


    /*Creates a new key, stores it, then returns key parameters and attributes to the client.
    The create key operation can be used to create any key type in Azure Key Vault.
    If the named key already exists, Azure Key Vault creates a new version of the key.
    It requires the keys/create permission.
    -------------------------------------------------------------------------------
    POST {vaultBaseUrl}/keys/{key-name}/create?api-version=2016-10-01
    Request Body: kty{RSA,EC}, key_size{int}
    --------------------------------------------------------------------------------
*/
    public function create(string $keyName, string $keyType, string $keySize)
    {
        $apiCall = "keys/{$keyName}/create?api-version=2016-10-01";
        
        $options = [
            'kty' => $keyType,
            'key_size' => $keySize
        ];

        return $this->requestApi('POST', $apiCall, $options);

    }

    /* Gets the public part of a stored key.
    The get key operation is applicable to all key types.
    If the requested key is symmetric, then no key material is released in the response.
    This operation requires the keys/get permission.
    --------------------------------------------------------------------------------
    GET {vaultBaseUrl}/keys/{key-name}/{key-version}?api-version=2016-10-01
    --------------------------------------------------------------------------------
    */
    public function get(string $keyName)
    {

        $apiCall = "keys/{$keyName}?api-version=2016-10-01";
        $response = $this->requestApi('GET', $apiCall);

        return $response;
    }


    /* List keys in the specified vault.
    Retrieves a list of the keys in the Key Vault as JSON Web Key structures that contain the public part of a stored key. 
    The LIST operation is applicable to all key types, however only the base key identifier, attributes, and tags are provided in the response. 
    Individual versions of a key are not listed in the response. 
    This operation requires the keys/list permission.
    --------------------------------------------------------------------------------
    GET {vaultBaseUrl}/keys?api-version=7.0
    --------------------------------------------------------------------------------
    */
    public function listKeys()
    {

        $apiCall = "keys?api-version=2016-10-01";
        $response = $this->requestApi('GET', $apiCall);

        return $response;
    }

    /*Creates a signature from a digest using the specified key.
    The SIGN operation is applicable to asymmetric and symmetric keys stored in Azure Key Vault since
    this operation uses the private portion of the key.
    This operation requires the keys/sign permission.
    --------------------------------------------------------------------------------
    POST {vaultBaseUrl}/keys/{key-name}/{key-version}/sign?api-version=2016-10-01
    Request Body: alg{signing/verification algorithm identifier}, value{string}
    --------------------------------------------------------------------------------
    */
   public function sign(string $keyID, string $algorithm, string $value)
   {

       $kID = substr($keyID, strpos($keyID, "/keys/")+1);
       $apiCall =  $kID."/sign?api-version=2016-10-01";

       $options = [
           'alg' => $algorithm,
           'value' => $value
       ];

       return $this->requestApi('POST', $apiCall, $options);
   }

   /*Verifies a signature using a specified key.
    The VERIFY operation is applicable to symmetric keys stored in Azure Key Vault. 
    VERIFY is not strictly necessary for asymmetric keys stored in Azure Key Vault since
    signature verification can be performed using the public portion of the key but
    this operation is supported as a convenience for callers that only have a key-reference and not the public portion of the key.
    This operation requires the keys/verify permission.
    --------------------------------------------------------------------------------
    POST {vaultBaseUrl}/keys/{key-name}/{key-version}/verify?api-version=7.0
    Request Body: alg{signing/verification algorithm identifier}, value{string}
    --------------------------------------------------------------------------------
    */
    public function verify(string $keyID, string $algorithm,string $digest, string $value)
    {
       $kID = substr($keyID, strpos($keyID, "/keys/")+1);
       $apiCall =  $kID."/verify?api-version=2016-10-01";

       $options = [
           'alg' => $algorithm,
           'digest' => $digest,
           'value' => $value

       ];

       return $this->requestApi('POST', $apiCall, $options);
    }
}