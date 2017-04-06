<?php require '/var/www/html/vendor/autoload.php';
require_once("/var/www/stock_BlueSky/constants.php");
include("queries.php");

//Chart Section
//New Mysqli Connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "collective2");
if (!$db) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
use \Curl\Curl;

$curl = new Curl();
 $arr = json_encode(array(
   "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
   "systemid" => "109963544",
   "signal" => array(
          "market" => 1,
  "symbol" => "AAPL",
  "typeofsymbol" => "stock",
  "action"=> "BTO",
  "limit" => "99.99",
  "duration" => "DAY",
  "quant" => 2),
));
  // $arr = json_encode($arr);
  // var_dump($arr);die;
// $curl->get('https://api.collective2.com/world/apiv3/getSystemDetails', $arr);
// $curl->get('https://api.collective2.com/world/apiv3/requestMarginEquity', $arr);

// $curl->setHeader('apikey', 'HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e');
// $curl->setHeader('systemid', '109963544');
// $arr = {
//  "apikey" : "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
//  "systemid": "109963544",
//  "signal":{
//    	"market": "1",
//   	"symbol": "AAPL",
//   	"typeofsymbol": "stock",
//   	"action": "BTO",
//   	"limit": "99.99",
//   	"duration": "DAY",
//   	"quant": 2
// 	}
// };

$curl->setHeader('Content-Type', 'application/json');
// $curl->setOpt('signal', $sig);
$curl->verbose();
$curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);

$response = $curl->response;
echo "kjhjk". var_dump($response)."\n";
die;
$result = $db->query($truncate_trades);
if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    $response = $curl->response;
    foreach ($response->response as $r) {
        $insert_trades = build_query($r);
        $result = $db->query($insert_trades);
        _db_error_test($result, $db, "56");
    }
}

//clean
$result = $db->query($truncate_local_stocks);
_db_error_test($result, $db, "39");

//move
$result = $db->query($move_to_local);
_db_error_test($result, $db, "43");

// bring in SO algo
$result = $db->query($update_algo);
_db_error_test($result, $db, "51");

//get current price
$result = $db->query($update_price);
_db_error_test($result, $db, "55");

//calc profit
$result = $db->query($update_profit_long);
_db_error_test($result, $db, "58");

//calc profit short
$result = $db->query($update_profit_short);
_db_error_test($result, $db, "61");

//calc trade duration
$result = $db->query($update_trade_duration);
_db_error_test($result, $db, "64");

//calc trade duration
$result = $db->query($update_long_delta);
_db_error_test($result, $db, "68");

//calc trade duration
$result = $db->query($update_short_delta);
_db_error_test($result, $db, "72");

//Update triggers
$result = $db->query($update_long_trigger);
_db_error_test($result, $db, "76");

$result = $db->query($update_short_trigger);
_db_error_test($result, $db, "79");

function _db_error_test($results, $db, $line = null)
{
    if (!$results) {
        echo "ERROR:send_signals.php" . "\n";
        echo "Line Number " . $line . "\n";
        echo mysqli_error($db) . "\n";
        die;
    }
}
// signal =>  array(action=>"SSHORT")
// key :HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e
// signalID 109603865
//Added composer command
// composer require php-curl-class/php-curl-class
// https://packagist.org/packages/php-curl-class/php-curl-class
