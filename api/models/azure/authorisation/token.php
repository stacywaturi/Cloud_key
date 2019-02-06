<?php

/*
*
* Allows you to get an access tokens from Azure AD.
*
* No Error handling on incorrect details.
*
* @author stacy
* @date 2018-11-12
*
*/

namespace Azure\Authorisation;

use GuzzleHttp\Client as HttpClient;
class Token
{

    /*
     * Get an authentication token from Azure AD for Azure Key Vault.
    */

    public static function getKeyVaultToken(array $azureAppDetails)
    {
        $guzzle = new HttpClient();

        $token = $guzzle->post(
            "https://login.windows.net/{$azureAppDetails['appTenantDomainName']}/oauth2/token",
            [
                'form_params' => 
                [
                    'client_id'     => $azureAppDetails['clientId'],
                    'username'      => $azureAppDetails['username'],
                    'password'      => $azureAppDetails['password'],
                    'resource'      => 'https://vault.azure.net',
                    'grant_type'    => 'password',
                ]
            ]
        )->getBody()->getContents();

        return json_decode($token, true)['access_token'];

    }
}
