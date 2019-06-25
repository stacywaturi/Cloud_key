<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/04
 * Time: 14:51
 */

function base64_url_encode( $data ) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$data = "0ggog30Mre9gOby6nte0Af36syvjeSaI/oOHV1gkqyw=";
var_dump(base64_encode(base64url_decode($data)));

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

