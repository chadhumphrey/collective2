<?php
/**
**
This script sends options trades, from the options out of sync table.
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

require_once("/var/www/html/collective2/calc.php");
$calc = new CALCULATION();

//Get options one month in advance.
$next_month = date('m', strtotime('+1 months'));

$result = $db->query($send_out_sync_options);
$calc->db_error_test($result, $db, "29");


foreach ($result as $r) {
    $day = 16;
    $year = 17;
    $action = $r['putcall'];
    $strike = $r['new_strike'];

    $option_month_query =$calc->option_symbol_query($action, $next_month);
    $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
    $calc->db_error_test($result, $db, "49");
    $C2_option_symbol = strtoupper($r['underlying']).$year.$day.$C2_option_month_symbol.$strike;

    $transaction = $r['new_transaction'];
    $condition = $r['new_condition'];
    $target_price = $r['new_target_price'];
    $quant = $r['new_quant'];

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
            "quant" => $quant
          ),
        )
      );

    $curl->setHeader('Content-Type', 'application/json');
    $curl->verbose();
    $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
    $response = $curl->response;
    var_dump($response)."\n";
}
