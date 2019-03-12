<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/08
 * Time: 09:49
 */

namespace Docsigner;


abstract class DocumentSigner

{

    private $baseUrl;

    public function __construct(array $apiDetails)
    {
        //set API details -- base URL
        $this->baseUrl = $apiDetails['baseUrl'];

    }


    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /*
    * Set the name of the key vault you want to interact with
    */


    private function setBaseUrl($baseUrlName)
    {
        $this->baseUrl =  baseUrlName;
    }

    /*
    * Create the API call to the Azure RM API
    */

    protected function requestApi($method, $apiCall, $json = null)
    {
        $client = new \GuzzleHttp\Client(
            [
                'base_uri'    => $this->baseUrl,
                'timeout'     => 10.0
            ]
        );

        try {
            $result = $client->request(
                $method,
                $apiCall,
                [
                    'headers' => [
//                        'User-Agent'    => 'browser/1.0',
//                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json'
////                        'Authorization' => "Bearer " . $this->accessToken
                    ],
                    'json' => $json
                ]
            );

           // return $result;
            return $this->setOutput(
                $result->getStatusCode(),
                $result->getReasonPhrase(),
//                $result->getBody()->getContents()
                json_decode($result->getBody()->getContents(), true)
            );

        } catch (\GuzzleHttp\Exception\ClientException $e) {
//            $jsonDecode = json_decode($e->getResponse()->getBody()->getContents(), true);
//            $arrayShift = array_shift($jsonDecode);
//            return $this->setOutput(
//                $e->getResponse()->getStatusCode(),
//                $arrayShift
//
//            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
//            return $this->setOutput(
//                500,
//                $e->getHandlerContext()['error']
//            );
        }
    }

    /*
    * Create an array to control output
    */
    private function setOutput($code, $message, $data = null)
    {
        return [
            'responsecode'    => $code,
            'responseMessage' => $message,
            'data'            => $data
        ];
    }

}