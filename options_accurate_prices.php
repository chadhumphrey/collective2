<?php
/**
**
This script will provide accurate options prices

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

$result = $db->query($get_all_open_options);
$calc->db_error_test($result, $db, "25");

foreach ($result as $r) {
    $realOptionSymbol = $calc->eval_price($r);

    echo $realOptionSymbol . "\n";
    $table = strtolower($r['underlying'])."_options";
    echo $q= "select * from options2017.$table where symbol = '$realOptionSymbol';";
    $optionData = $db->query($q);
    while ($obj = $optionData->fetch_object()) {
        // $ask = $obj->ask;
      // $bid = $obj->bid;
      $midPrice = ($obj->ask + $obj->bid)/2;
    }

    /*$bid = $optionPrice->fetch_object()->bid;
    $ask = $optionPrice->fetch_object()->ask;
    echo "--> ". $bid . "\n";*/
    // echo "midPrice--> ". $midPrice . "\n";
    // echo "start price --> ". $r['opening_price_VWAP'] . "\n";
    //
    // $profit = (($midPrice - $r['opening_price_VWAP']) / $r['opening_price_VWAP']) *100;
    // echo "% profit--> ". $profit . "\n";

    $realOptionSymbol = $calc->update_price($r, $midPrice);

    // var_dump($optionPrice->fetch_object());

    // die;
}

//   switch (true) {
//     case ($r['putcall'] == 'call' && $r[long_or_short] == 'long'):
//       $calc->eval_price($r);
//       break;
//
//     default:
//       # code...
//       break;
//   }
//     $day = 16;
//     $year = 17;
//     $action = $r['putcall'];
//     $strike = $r['new_strike'];
//
//     $option_month_query =$calc->option_symbol_query($action, $next_month);
//     $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
//     $calc->db_error_test($result, $db, "49");
//     $C2_option_symbol = strtoupper($r['underlying']).$year.$day.$C2_option_month_symbol.$strike;
//
//     $transaction = $r['new_transaction'];
//     $condition = $r['new_condition'];
//     $target_price = $r['new_target_price'];
//     $quant = $r['new_quant'];
//
// //build signal array
//    $arr = json_encode(array(
//    "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
//    "systemid" => "109963544",
//    "signal" => array(
//             "action" => "$transaction",
//             "symbol" => "$C2_option_symbol",
//             "typeofsymbol" => "option",
//             "$condition" => "$target_price",
//             "duration" => "DAY",
//             "quant" => $quant
//           ),
//         )
//       );
//
//     $curl->setHeader('Content-Type', 'application/json');
//     $curl->verbose();
//     $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
//     $response = $curl->response;
//     var_dump($response)."\n";
// }
