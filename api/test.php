<?php
$cert1 ="-----BEGIN CERTIFICATE-----
MIIDszCCApugAwIBAgIJALdDZpg/6pOYMA0GCSqGSIb3DQEBCwUAMHAxCzAJBgNVBAYTAlpBMRAwDgYDVQQIDAdHYXV0ZW5nMRUwEwYDVQQHDAxKb2hhbm5lc2J1cmcxGzAZBgNVBAoMEmlTb2x2IFRlY2hub2xvZ2llczEbMBkGA1UEAwwSSmFzaGluIFNlbGYtU2lnbmVkMB4XDTE5MDEyMzE0MTQxMloXDTIwMDEyMzE0MTQxMlowcDELMAkGA1UEBhMCWkExEDAOBgNVBAgMB0dhdXRlbmcxFTATBgNVBAcMDEpvaGFubmVzYnVyZzEbMBkGA1UECgwSaVNvbHYgVGVjaG5vbG9naWVzMRswGQYDVQQDDBJKYXNoaW4gU2VsZi1TaWduZWQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDB5mLxmWib4FCyTjaNoXjjUzi/SifqWMOPacOHqPUUO2e2Q7yFQICoswnjn4hBU53wOwQ4z6+aqYfbyZnGySNFicRU+GpuzyYFlROgoWZSG7YiT1cdVMi/TwHzuNMv0W8wRYANcdEnUE6HvSvPmjXSHVqvnAoYJ/s0bqvsxzPBN//5xfVmtbTROWdi3IgOLvreoFg82gGyBhIeO8tlLeM4JoIITe4I0ICflDm+7kEQ2yAWLkKFsg2rCPbmnzKnFPXDYT130OIA8PxhINAivmYZYaSZNm2s2knioComMTyqCzxUdIJHxqXV3VFGELQvFfOTn8vx7e4cz2+gPWJpznBjAgMBAAGjUDBOMB0GA1UdDgQWBBTgTer9hsd68rtEU+KbRyznriPTsjAfBgNVHSMEGDAWgBTgTer9hsd68rtEU+KbRyznriPTsjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQA4IVksqk3yLPgte2G1xjwaTA21lURuhUHh1Hzylsd0yYYIjWsAFKXkf9yDSecm561EV6/9TG/4RmkXfkQCij+OxpTsdj4xT7HOFW0q5KMfmVyQMdtOJXDzcaoOhP95rIVR/5uZrnowFLlXDtJW6DV8VBUk5rU6/7hX9/6X1KPE+G/tQN81TMLBnxxElTk8cJVKhhDd8tDoODptyr6XG6cYsrdZ3oYcbqPaUHvmnPO2NNxlHhKCczj3Qx+ANBPPpzg0BwJRRl9cr4ImzswzLqsgL/lGfqNviojYGG+0So3VzYdnoGNWriTAkjrGDA4DpnG27+MjdzKMTO1wcqfxqcNr
-----END CERTIFICATE-----";
$cert =  $cert = "-----BEGIN CERTIFICATE-----\r\n". $cert1. "\r\n-----END CERTIFICATE-----";

$resource = openssl_pkey_get_public($cert1);
var_dump($resource);
if($resource) {
    $array =openssl_pkey_get_details($resource);
    $hex = array_map("base64_url_encode", $array["rsa"]);

    var_dump($array);
    var_dump($hex);
}


$cert_attributes = openssl_x509_parse($cert1);

var_dump($cert_attributes);
var_dump($cert_attributes['subject']);
var_dump($cert_attributes['issuer']);
var_dump($cert_attributes['serialNumber']);
var_dump($cert_attributes['validTo_time_t']);


function base64_url_encode( $data ) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$data = "dzBdiTrcevEQCJBC0LLrTIvTfld5FATfvuGHLI_WmDT_yciorDih6zpw9MwXpnBPz-PCMP4b8W9CJnJXwA2leeEVFgBJqfOp13sSRhKwzksds5ucCqXUdrxOLgQPhfzs3EwJr3USdqvaZL28W8W99AWXyGzOnRWNRC6gOHHU9NKqoGeyvVAIg_ezvB3w1CjXMQQ72Gjo2UMY0EYGyFq-ZI_X6xEkAs2ng7JI7-9iXS5a8lfhRyxLnPlh-0IUGriFdMdT1oJtizExLstT1bgzmVP7ddaB_dS6knZfmrQwK2VwSBQweBU-d-dJUgwpUI-dVI2dz-mAqnmbndJq7tdmFw";
var_dump(base64_encode(base64url_decode($data)));

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
//var_dump(uniqid(rand(), false));