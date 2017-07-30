<?php
/**
**
This script will provide daily safety trades for options.
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
    $x = str_replace($r['underlying'], "", $r['symbol']);
    $year = substr($x, 0, 2);
    $day = substr($x, 2, 2);
    $tempMonth = $x[4];
  // $month[5] = 5, 5);

    echo $q="select id from options_symbols where `call` = '$tempMonth';";
    $month = $db->query($q)->fetch_object()->id;
    echo $month . "<----\n";
    if (strlen($month<2)) {
        echo "single digit\n";
        $month = sprintf("%02d", $month);
    }
    if (strlen($r['strike'])==2) {
        $strike = "000".$r['strike']."000";
    }
  // $C2_option_symbol = strtoupper(trim($r['equity'])).$year.$day.$C2_option_month_symbol.$strike;
  // $d = date_parse($exDate);
  //     $year = date('y', strtotime($exDate));
echo "Strike---->".$strike . "\n";
echo "Day---->".$day . "\n";
$option = "C";
//BMY170728C00080000

$realOptionSymbol = $r['underlying'].$year.$month.$day.$option.$strike;

    echo $realOptionSymbol;
    echo $q= "select * from options2017.bmy_options where symbol = '$realOptionSymbol';";
    $optionPrice = $db->query($q);
    var_dump($optionPrice->fetch_object());

    die;
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
