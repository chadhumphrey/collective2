<?php

echo "Why this should be here??" ; die;

 require '/var/www/html/vendor/autoload.php';
require_once("/var/www/html/stocks/constants.php");
include("/var/www/html/collective2/queries.php");

require_once("/var/www/html/collective2/calc.php");
$calc = new CALCULATION();
use \Curl\Curl;

$curl = new Curl();

error_reporting(E_ALL);

//New Mysqli Connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "collective2");
if (!$db) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

//clear historic trades
$result = $db->query('truncate trades');
$calc->db_error_test($result, $db, "24");

//Get user input on tables
$systemId = $calc->get_system($argv[1]);
$systemTable = $calc->get_system_table($argv[1]);

$arr = array(
  'apikey' => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
  'systemid' => $systemId
  );
// $curl->get('https://api.collective2.com/world/apiv3/getSystemDetails', $arr);
// $curl->get('https://api.collective2.com/world/apiv3/requestMarginEquity', $arr);
$curl->get('https://api.collective2.com/world/apiv3/requestTrades', $arr);

$result = $db->query($truncate_trades);
if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    $response = $curl->response;
    foreach ($response->response as $r) {
        $insert_trades = build_query($r);
        $result = $db->query($insert_trades);
        $calc->db_error_test($result, $db, "56");
    }
}

//clean
$result = $db->query($truncate_local_stocks);
$calc->db_error_test($result, $db, "39");

//move
$result = $db->query($move_to_local);
$calc->db_error_test($result, $db, "43");

// bring in SO algo
$result = $db->query($update_algo);
$calc->db_error_test($result, $db, "51");

//get current price
$result = $db->query($update_price);
$calc->db_error_test($result, $db, "55");

//calc profit
$result = $db->query($update_profit_long);
$calc->db_error_test($result, $db, "58");

//calc profit short
$result = $db->query($update_profit_short);
$calc->db_error_test($result, $db, "61");

//calc trade duration
$result = $db->query($update_trade_duration);
$calc->db_error_test($result, $db, "64");

//calc trade duration
$result = $db->query($update_long_delta);
$calc->db_error_test($result, $db, "68");

//calc trade duration
$result = $db->query($update_short_delta);
$calc->db_error_test($result, $db, "72");

//Update triggers
$result = $db->query($update_long_trigger);
$calc->db_error_test($result, $db, "76");

$result = $db->query($update_short_trigger);
$calc->db_error_test($result, $db, "79");

//Options
$result = $db->query($truncate_opt);
$calc->db_error_test($result, $db, "83");

$result = $db->query($move_to_options);
$calc->db_error_test($result, $db, "86");

$result = $db->query($update_algo_options);
$calc->db_error_test($result, $db, "89");

//Section below is where the tables / systems split
$optTable = $systemTable."_opt";
$q = "truncate $systemTable  ";
$result = $db->query($q);
$calc->db_error_test($result, $db, "102");

$q = "truncate $optTable  ";
$result = $db->query($q);
$calc->db_error_test($result, $db, "108");

echo $q = "insert into $systemTable () select * from local_stocks; ";
$result = $db->query($q);
$calc->db_error_test($result, $db, "113");

echo $q = "insert into $optTable () select * from opt; ";
$result = $db->query($q);
$calc->db_error_test($result, $db, "117");



/*Notes*/
// signal =>  array(action=>"SSHORT")
// key :HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e
// signalID 109603865
//Added composer command
// composer require php-curl-class/php-curl-class
// https://packagist.org/packages/php-curl-class/php-curl-class
