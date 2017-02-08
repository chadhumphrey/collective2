<?php require '../vendor/autoload.php';

use \Curl\Curl;

$curl = new Curl();
$curl->get('https://api.collective2.com/world/apiv3/getSystemRoster');

if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}
//Added composer command
// composer require php-curl-class/php-curl-class
// https://packagist.org/packages/php-curl-class/php-curl-class
