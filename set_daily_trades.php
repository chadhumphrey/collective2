<?php
/**
**
This script sets exit points for all stocks, regardless how far they are from the trigger point.
**/
 require '/var/www/html/vendor/autoload.php';
require_once("/var/www/stock_BlueSky/constants.php");
include("queries.php");


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

$result = $db->query($set_all_daily_trade_trigger);
_db_error_test($result, $db, "24");

foreach ($result as $r) {
    switch ($r['long_or_short']) {
      case 'long':
        $action = "STC";
        $condition = "stop";
        $exit_price = $r['girt_mn_sell'];
        break;
      case 'short':
        $action = "BTC";
        $condition = "stop";
        $exit_price = $r[girt_mn_buy];
        break;
      default:
        break;
  }
    echo $r['symbol'];

//build signal array
    $arr = json_encode(array(
   "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
   "systemid" => "109963544",
   "signal" => array(
            "action" => "$action",
            "symbol" => "$r[symbol]",
            "typeofsymbol" => "stock",
            "$condition" => "$exit_price",
            "duration" => "DAY",
            "quant" => $r[quant_opened],
            "conitionalupon"=>$r['trade_id']
          ),
        )
      );

      $curl->setHeader('Content-Type', 'application/json');
      $curl->verbose();
      $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
      $response = $curl->response;
      var_dump($response)."\n";

}
