<?php
/**
**
This script open options trades
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

$next_month = date('m', strtotime('+1 months'));
//echo "blah--->" .$next_month . "\n";


//$month_code = $db->query($option_query);
// _db_error_test($result, $db, "32");
//echo "month_code ". $next_month;
// $option_query =_option_symbol_query($act, $next_month);

$result = $db->query($open_option_trades);
_db_error_test($result, $db, "24");
foreach ($result as $r) {
    switch ($r['action']) {
      case 'buy':
        $action = "call";
        break;
      case 'sell':
        $action = "put";
        break;
      default:
        break;
  }
  $day = 19;
  $year = 17;
  $option_month_query =_option_symbol_query($action, $next_month);
  $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
  // $C2_option_symbol = $result->fetch_object()->symbol;
  _db_error_test($result, $db, "49");
  echo "----> ". $C2_option_month_symbol . "\n";
  $C2_option_symbol = strtoupper($r['calc_equity']).$year.$day.$C2_option_month_symbol."65";
  echo $C2_option_symbol . "\n";
  die;

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
