<?php require '../vendor/autoload.php';

use \Curl\Curl;

$curl = new Curl();
$arr = array(
  apikey => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
  systemid => "109603865",
  );
// $curl->get('https://api.collective2.com/world/apiv3/getSystemDetails', $arr);
$curl->get('https://api.collective2.com/world/apiv3/getSystemDetails', $arr);


if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}

// key :HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e
// signalID 109603865
//Added composer command
// composer require php-curl-class/php-curl-class
// https://packagist.org/packages/php-curl-class/php-curl-class
