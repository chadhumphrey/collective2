<?php
/**
**
This script uses the opt_out_of_sync table to make adjustments to options trades.
**/
error_reporting(E_ALL);
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
$result = $db->query($options_out_of_sync);
$calc->db_error_test($result, $db, "26");
foreach ($result as $r) {
   switch ($r['putcall']) {
      case 'call':
        $strike = $r['strike'] +5;
        $quant = $r['quant_opened'];
        $action = "call";
        $condition = "limit";
        $transaction = "STO";
        $target_price = $calc->get_mid_price(strtolower($r['underlying']), $strike, "calls");
        break;
      case 'put':
      echo "put";
        $strike = $r['strike']-5;
        $quant = $r['quant_opened'];
        $action = "put";
        $condition = "limit";
        $transaction = "STO";
        $target_price = $calc->get_mid_price(strtolower($r['underlying']), $strike, "puts");

        break;
      default:
        break;
  }
    $next_month = 6;
    $day = 16;
    $year = 17;

    if($target_price === null){
      continue;
    }

    $u = "update opt_out_of_sync set new_quant = '$quant', new_transaction ='$transaction', new_condition ='$condition', new_target_price ='$target_price',
    new_strike = '$strike' where id = '$r[id]'";
    $C2_option_month_symbol = $db->query($u);
    $calc->db_error_test($result, $db, "49");

}
// echo "this is target_price ". $target_price . " \n";
// die;

    /*$option_month_query =$calc->option_symbol_query($action, $next_month);
    $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
    $calc->db_error_test($result, $db, "49");
    $C2_option_symbol = strtoupper($r['calc_equity']).$year.$day.$C2_option_month_symbol.$strike;*/


//build signal array
   /*$arr = json_encode(array(
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

    $curl->setHeader('Content-Type', 'application/json');
    $curl->verbose();
    $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
    $response = $curl->response;
    var_dump($response)."\n";*/
// }
