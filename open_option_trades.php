<?php
/**
**
This script open options trades, only buys Calls & Puts does not sell to open.
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

//Get options one month in advance.
$next_month = date('m', strtotime('+1 months'));


//$month_code = $db->query($option_query);
// _db_error_test($result, $db, "32");
//echo "month_code ". $next_month;
// $option_query =_option_symbol_query($act, $next_month);

$result = $db->query($open_option_trades);
$calc->db_error_test($result, $db, "24");


foreach ($result as $r) {
    switch ($r['action']) {
      case 'buy':
      $strike = _get_strike($r['close_results'], "call");
        $action = "call";
        $condition = "limit";
        $transaction = "BTO";
        $target_price = _get_mid_price($r['calc_equity'], $strike, "calls");
        break;
      case 'sell':
      $strike = _get_strike($r['close_results'], "put");
        $action = "put";
        $condition = "limit";
        $transaction = "BTO";
        $target_price = _get_mid_price($r['calc_equity'], $strike, "puts");
        break;
      default:
        break;
  }
    $day = 19;
    $year = 17;

// echo "this is target_price ". $target_price . " \n";
// die;

    $option_month_query =_option_symbol_query($action, $next_month);
    $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
    $calc->db_error_test($result, $db, "49");
    $C2_option_symbol = strtoupper($r['calc_equity']).$year.$day.$C2_option_month_symbol.$strike;


//build signal array
   $arr = json_encode(array(
   "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
   "systemid" => "109963544",
   "signal" => array(
            "action" => "$transaction",
            "symbol" => "$C2_option_symbol",
            "typeofsymbol" => "option",
            "$condition" => "$target_price",
            "market" => 1,
            "duration" => "DAY",
            "quant" => 1
          ),
        )
      );

    $curl->setHeader('Content-Type', 'application/json');
    $curl->verbose();
    $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
    $response = $curl->response;
    var_dump($response)."\n";
}
