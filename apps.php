<?php require '../vendor/autoload.php';

use \Curl\Curl;

$curl = new Curl();
// $curl->get('https://api.collective2.com/world/apiv3/getSystemDetails', $arr);
$curl->get('https://itunes.apple.com/search?term=yelp&country=us&entity=software');


if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    //var_dump($curl->response);
    $data = json_decode($curl->response);
    foreach ($data->results as $val){
     echo "hello --->" .$val->trackId . "\n";
     echo "hello --->" .$val->trackId . "\n";

      // echo "hello --->" .var_dump($val) . "\n";

      //var_dump($val);
      // echo $val->trackId . "\n";
    }
}

// key :HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e
// signalID 109603865
//Added composer command
// composer require php-curl-class/php-curl-class
// https://packagist.org/packages/php-curl-class/php-curl-class
