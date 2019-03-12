<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/08
 * Time: 09:59
 */

namespace DocSigner;


class CloudKeyDocSigner extends DocumentSigner
{
    public function __construct(array $apiDetails)
    {
        parent::__construct($apiDetails);
    }
    /* Expected return: String type - This server is running !!!
    */

    public function test()
    {
        $apiCall = "test";

        var_dump($this->getBaseUrl());
        $response = $this->requestApi('GET', $apiCall);

        return $response;
    }


    /*Get List of Signature Blocks in a given document
     URL: /SigningOperationsResource/GetSignatureBlocks
     Request Body - JSON Object with the following properties:
    document_reference – Specifies a document reference
    service_id – for now will be localFileSystem, if other methods are introduced

   -------------------------------------------------------------------------------
     Example of JSON body:
    {
    "document_reference": "C:\\Users\\Nishant.CORP\\Desktop\\test_sign - Copy.docx",
    "service_id": "localFileSystem"
    }
     Expected return: JSON Object with the following properties:
    200 OK
    412 Percondition Failed - With error message
    400 Bad Request – With error message
    Status – either true or false
    Error – if false status, an error description should appear here
    Array of Signature Block Objects
   --------------------------------------------------------------------------------
*/
    public function getSignatureBlocks(string $documentReference)
    {
        $apiCall = "GetSignatureBlocks";
        $serviceId =  "localFileSystem";
        $options = [
            'document_reference' => $documentReference,
            'service_id' => $serviceId
        ];

        return $this->requestApi('POST', $apiCall, $options);

    }

    /*URL: http://localhost:8080/SigningOperationsResource/GetDigestValue
     Request Body - JSON Object with the following properties:
    signature_setup_id – an ID value from the returned list of signure blocks.
    signature_text – Signature text if no signature image is specified
    document_reference – Specifies a document reference
    signature_image_reference - Specifies an image reference
    digest_algorithm – the algorithm used to produce the digest value (SHA256…)
    service_id - for now will be localFileSystem, if other methods are introduced
    certificate_string – the base64 encoded cert string (without begin & end tags)

   -------------------------------------------------------------------------------
     Example of JSON body:
    {
    "signature_setup_id": "{7EF17A08-09CB-4833-AC51-49B01912B679}",
    "signature_tex": "This is a signature",
    "document_reference": "C:\\Users\\Nishant.CORP\\Desktop\\test_sign - Copy.docx",
    "signature_image_reference": "C:\\Users\\Nishant.CORP\\Desktop\\signature.png",
    "digest_algorithm": "SHA256",
    "service_id": "localFileSystem",
    "certificate_string": "MIIDszCCApugAwIBAgIJALdDZpg/6pOYMAR6/9TG/4...."
     Expected return: JSON Object with the following properties:
    200 OK
    412 Percondition Failed - With error message
    400 Bad Request – With error message
    Status – either true or false
    Error – if false status, an error description should appear here, else null
    digestValueBase64 – Base 64 encoded digest value. This is what will be signed.

   --------------------------------------------------------------------------------
*/
    public function getDigestValue(string $signatureSetupId, string $documentReference, string $certificate)
    {
        $apiCall = "GetDigestValue";

        $signatureTex =  "This is a signature";
        $signatureImageReference =  null;
        $digestAlgorithm=  "SHA256";
        $serviceId =  "localFileSystem";
        $options =
        [
            'signature_setup_id'=> $signatureSetupId,
            'signature_tex' => $signatureTex,
            'document_reference' =>$documentReference,
            'signature_image_reference'=> $signatureImageReference,
            'digest_algorithm' =>$digestAlgorithm,
            'service_id'=> $serviceId,
            'certificate_string'=> $certificate
        ];

        return $this->requestApi('POST', $apiCall, $options);

    }

    /*URL: http://localhost:8080/SigningOperationsResource/InsertSignedValue
 Request Body - JSON Object with the following properties:
digest_value_base64 – the digest value as a reference. Was returned from the previous call.
signed_value – This is the signed value after signing the digest

-------------------------------------------------------------------------------
 Example of JSON body:
{
"digest_value_base64": "kvULLbSECwaD2z+JtSv8EbglRtGZ+A8ixGvbflKUwog=",
"signed_value": "hYFTYE1JzTQMyBAGSWi.....ETd+VmobvQroaj2JG5Sb1gv8KAJbquevkCSw=="
}
 Expected return: JSON Object with the following properties:
200 OK
400 Bad Request – With error message
status – either true or false
error – if false status, an error description should appear here, else will be null
 Returned JSON Object Example:
{
"status": false,
"error": null
}

--------------------------------------------------------------------------------
*/
    public function insertSignedValue($digestValue, $signedValue)
    {
        $apiCall = "InsertSignedValue";

        $options =
            [
                'digest_value_base64'=> $digestValue,
                'signed_value' => $signedValue

            ];


        return $this->requestApi('POST', $apiCall, $options);

    }





}