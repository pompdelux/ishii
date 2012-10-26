<?php

use Ishii\Application;

function _log($data, $back = 0) {
    $bt = debug_backtrace();
    $line = $bt[$back]['line'];
    $file = str_replace(realpath(__DIR__ . '/../../'), '~', $bt[$back]['file']);
    error_log($file.' +'.$line.' :: '.print_r($data, 1));
}

//https://developers.facebook.com/docs/howtos/login/signed-request/
function parse_signed_request($signed_request) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}


$app = new Application(array('debug' => true));
$app['debug'] = true;

$app->mount('/gallery', new Ishii\Apps\Gallery\Controller());

return $app;
