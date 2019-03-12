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

$data = "Sk2OJuNiDDkJckks4RDfe8yoyAAt7UA3rSqI-RazLpjF7GZCBLwiCtysk20d-GQzwyJPATqK4Qm1_Xx-y1FXB4QwmIkIJsWeBY7_qBj2TW7VFoAUnp5V123MFPBfXiybx26WysHYKGv4AtZLPUg1YXgT1aaiclr7FDRXigQh1xPKDZEtoKZTqGVUr0RbUPvHb5vFvAycv-m1jUSBQNiXysoaqTd33TwEsAZGMK1qq8HU934_PQvnKMGKDj-pzTVAUh9OlZ7kVSdiBkm5VVX8PO1gq7EeSJKUS8uzO8IDhdHHHOxwI1Roys63KT3K387lDqx8OIUZ9is0G0LwEbyQ0Q";
var_dump(base64_encode(base64url_decode($data)));

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

