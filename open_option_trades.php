<?php
/**
**
This script open options trades, only buys Calls & Puts does not sell to open.
**/
require '/var/www/html/vendor/autoload.php';
require_once("/var/www/html/stocks/constants.php");
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

require_once("/var/www/html/collective2/calc.php");
$calc = new CALCULATION();

//Get options one month in advance.
$next_month = date('m', strtotime('+1 months'));

$result = $db->query($open_option_trades);
$calc->db_error_test($result, $db, "24");

$current_expiration = "2017-11-17";
$secondary_expiration = "2017-12-15";
foreach ($result as $r) {
    switch ($r['action']) {
      case 'buy':
        $strike = $calc->get_strike($r['close_results'], "call");
        $action = "call";
        $condition = "limit";
        $transaction = "BTO";
        $exDate = $calc->validate_expiration($r['calc_equity'], $strike,"call", $current_expiration, $secondary_expiration);
        $target_price = $calc->get_mid_price($r['calc_equity'], $strike, "call",$exDate);

        break;
      case 'sell':
        $strike = $calc->get_strike($r['close_results'], "put",$exDate);
        $action = "put";
        $condition = "limit";
        $transaction = "BTO";
        $exDate = $calc->validate_expiration($r['calc_equity'], $strike, "put",$current_expiration, $secondary_expiration);
        $target_price = $calc->get_mid_price($r['calc_equity'], $strike, "put",$exDate);

        break;
      default:
        break;
  }


    if($target_price === null XOR $target_price < 0){
      continue;
    }

    //Parse Expiration Date
    $d = date_parse($exDate);
    $year = date('y', strtotime($exDate));

    $option_month_query =$calc->option_symbol_query($action, $d['month']);
    $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
    $calc->db_error_test($result, $db, "49");
    $C2_option_symbol = strtoupper(trim($r['calc_equity'])).$year.$d['day'].$C2_option_month_symbol.$strike;


//build signal array
   $arr = json_encode(array(
   "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
   "systemid" => "109963544",
   "signal" => array(
            "action" => "$transaction",
            "symbol" => "$C2_option_symbol",
            "typeofsymbol" => "option",
            "$condition" => "$target_price",
            "duration" => "DAY",
            "quant" => 1
          ),
        )
      );
  // var_dump(json_decode($arr));
  // echo "\n\n\n\n";die;

    $curl->setHeader('Content-Type', 'application/json');
    /*$curl->verbose();*/
    $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
    $response = $curl->response;
    var_dump($response)."\n";
}
