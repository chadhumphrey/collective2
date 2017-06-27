<?php
/**
**
This script opens options tables and looks for IV and HV miss match.
**/
require_once("/var/www/stock_BlueSky/constants.php");
include("queries.php");

//New Mysqli Connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "options2017");
if (!$db) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}


require_once("/var/www/html/collective2/calc.php");
$calc = new CALCULATION();


$q = "truncate options_VOL;";
$result = $db->query($q);
$calc->db_error_test($result, $db, "25");

$q = "select * from equities_tracked ;";
$result = $db->query($q);
$number = mysqli_num_rows($result);
$xx =0;
if (($number) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo  $row['equity'] . "\n";
        $stock = $row['equity'];
        $q = "SELECT * FROM stocks_bluesky.all_stocks_alt, options2017.equities_tracked
        WHERE options2017.equities_tracked.equity = '$stock'
        AND stocks_bluesky.all_stocks_alt.date_results = CURRENT_DATE
        AND stocks_bluesky.all_stocks_alt.calc_equity = '$stock';";

        $r = $db->query($q);
        $calc->db_error_test($r, $db, "28");

        $price = $r->fetch_object()->close_results;

        $table = "options2017.".$stock."_options";
        //$q ='insert into options_VOL () SELECT * FROM '.$table.' where strike between '.($price - 10).' and '.($price + 10).' and ivol > (HV_20D + 5)';
        $q ='insert into options_VOL () SELECT * FROM '.$table.'
        WHERE
        last != 0
        and ivol > (HV_20D +10)
        and strike between current_price - 50 and current_price +50
        and "2017-06-23" != ex_date
        and "2017-06-30" != ex_date
        and last > 2
        and last - ((bid+ ask)/2) >1
        ORDER BY
        ex_date,equity DESC';

        $rr = $db->query($q);
        $calc->db_error_test($rr, $db, "49");
    }
}
// $number = mysqli_num_rows($result);
// if ($number > 0) {
//     while ($row = mysqli_fetch_assoc($result)) {
//       $table = "options2017.".$stock."_options";
//       echo $q ='SELECT * FROM '.$table.'' ;
//       $price = $result->fetch_object()->midprice;
//
//
//     }
// }




// $table = "options2017.".$stock."_options";
// $q ='SELECT * FROM '.$table.' where strike = '.$strike.' and opt_type = "'.$option.'" and ex_date = "'.$current_expiration.'"  limit 1';
// echo "-----> ". $q . " <-----";
// $result = $db->query($q);
// if (mysqli_num_rows($result) > 0) {
//     return $current_expiration;
// } else {
//     $q2 ='SELECT * FROM '.$table.' where strike = '.$strike.' and opt_type = "'.$option.'" and ex_date = "'.$secondary_expiration.'"  limit 1';
//     $result2 = $db->query($q2);
//     if (mysqli_num_rows($result2) > 0) {
//         return $secondary_expiration;
//     } else {
//         return "2018-01-19";
//     }
// }
//
// $current_expiration = "2017-11-17";
// $secondary_expiration = "2017-12-15";
// foreach ($result as $r) {
//     switch ($r['action']) {
//       case 'buy':
//         $strike = $calc->get_strike($r['close_results'], "call");
//         $action = "call";
//         $condition = "limit";
//         $transaction = "BTO";
//         $exDate = $calc->validate_expiration($r['calc_equity'], $strike, "call", $current_expiration, $secondary_expiration);
//         $target_price = $calc->get_mid_price($r['calc_equity'], $strike, "call", $exDate);
//
//         break;
//       case 'sell':
//         $strike = $calc->get_strike($r['close_results'], "put", $exDate);
//         $action = "put";
//         $condition = "limit";
//         $transaction = "BTO";
//         $exDate = $calc->validate_expiration($r['calc_equity'], $strike, "put", $current_expiration, $secondary_expiration);
//         $target_price = $calc->get_mid_price($r['calc_equity'], $strike, "put", $exDate);
//
//         break;
//       default:
//         break;
//   }
//
//
//     if ($target_price === null xor $target_price < 0) {
//         continue;
//     }
//
//     //Parse Expiration Date
//     $d = date_parse($exDate);
//     $year = date('y', strtotime($exDate));
//
//     $option_month_query =$calc->option_symbol_query($action, $d['month']);
//     $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
//     $calc->db_error_test($result, $db, "49");
//     $C2_option_symbol = strtoupper(trim($r['calc_equity'])).$year.$d['day'].$C2_option_month_symbol.$strike;
//
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
//             "quant" => 1
//           ),
//         )
//       );
//   // var_dump(json_decode($arr));
//   // echo "\n\n\n\n";die;
//
//     $curl->setHeader('Content-Type', 'application/json');
//     /*$curl->verbose();*/
//     $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
//     $response = $curl->response;
//     var_dump($response)."\n";
// }
