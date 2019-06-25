<?php
$cert1 ="-----BEGIN CERTIFICATE-----\r\nMIIELjCCAhYCAhAmMA0GCSqGSIb3DQEBCwUAMHcxCzAJBgNVBAYTAlpBMRAwDgYD\r\nVQQIDAdHYXV0ZW5nMRswGQYDVQQKDBJpU29sdiBUZWNobm9sb2dpZXMxFjAUBgNV\r\nBAMMDWlzb2x2dGVjaC5jb20xITAfBgkqhkiG9w0BCQEWEmluZm9AaXNvbHZ0ZWNo\r\nLmNvbTAeFw0xOTA1MDIwNzE1MzhaFw0yMDA1MTEwNzE1MzhaMEIxCzAJBgNVBAYT\r\nAlVTMRUwEwYDVQQKDAxFeGFtcGxlIEluYy4xHDAaBgNVBAMME2NlcnRpZmljYXRl\r\nMDQyOS5jb20wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCG0eRWJ+xQ\r\ngVJZUMpywgvCD58k1UXwylj5krHzFHGfCBPb0Wt8ZHrD+UuHeDOJ9UzMxQAm027G\r\nBl3mnWhgMMA7eCH/htkhG/1rYaZBTDnsV7+u+wRsC4AzXgTMksFalJ+ML7yYo7tR\r\n/bpsSe5G2wPpNaz65zIvLK/bGdMpadITEIhUxGbDCkROvcNctOk5nvOk0xrOJJkY\r\nbZHyzSLHEkksMcGkwASIKlBRCT0wNktklLPrPhgt5PHs1AbnAnejYTvddICP3Q9U\r\nCjBrIHcwvBre5lh19hWaRfOX9UpXz6XIHIaSTErkXW1kAdZqpZhfj/sgnN3ZeIo9\r\nvnyuis4nCGlPAgMBAAEwDQYJKoZIhvcNAQELBQADggIBACvVeQ9a1aivwfOX3613\r\nQ6l7d2eudRXfJ8g9e/tdlXPNjOLcwDsMbUSlghcH6r1+pZ9kxcvhbQwLX+vq7gHQ\r\n/VR5ddwUhVjYRH0WbgJTQpg8W+qF1NBay2EluPB7098a9nDAVUBBPTmnxM5mKb6U\r\nBm0vxIuRYX+TcdqpXxk0y5faX1Pa4AndGJsYFVdGkAs5P1EQ8NjLtG0B+z3KGQJ5\r\nzhR7kxEKK/sTC89uILt464wbsTymR8e7/vmUPWG2WyzwOSf3KyO54dnv5/Ioqbax\r\nfxnH1xyj4wTNY5uX2ULBc0+X/0A6spfUU+vQ9HbXkDTqKdAoxSxd9eQHY13lW1RN\r\nMSQUhhh9r99LXHCb8tOaUhxWWRcOinSxQemGGewIYaeMmTEguOTF/pqjxkddXilp\r\nbNi/CT0xrKdYCZndCbtWhgftQ+3E7OwWbNipEI2uSh5BcNI98ySuTEb8FnLa7aXn\r\nCiqVn9tESsNAE3Tg39XIWlg97Yzt2allfxCFYJUm5nGwv8BJSuZ43WAs5tw19lA6\r\nQfJkzQz7Uh7lf8l7g7Ji5iAzbKmQPPzZjfBj7C56QHh14QRSMHmfu/BFc9t5RKsj\r\nvl7c8TrZHcUU/AvRJG2m7oQNFVfdECyVwIjoJrNy9+cVELIBLNuFB7H4Otr9d+0u\r\nHzd2szYFH3cGZYG+Rfen9Qx5\r\n-----END CERTIFICATE-----";
$cert =  "-----BEGIN CERTIFICATE-----\r\n". $cert1. "\r\n-----END CERTIFICATE-----";

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