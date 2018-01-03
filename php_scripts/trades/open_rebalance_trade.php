<?php
/**
**
This script will create cover trades for short position that have gone the wrong way.
**/
require '/var/www/html/vendor/autoload.php';
require_once("/var/www/html/stocks/constants.php");
include("/var/www/html/collective2/queries.php");

use \Curl\Curl;

$curl = new Curl();

require_once("/var/www/html/collective2/calc.php");
$calc = new CALCULATION();


//New Mysqli Connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "collective2");
if (!$db) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

$systemId = $calc->get_system($argv[1]);
$systemTable = $calc->get_system_table($argv[1]);
$optTable = $systemTable."_opt";

error_reporting(1);

//Cancel existing trades
$q = "select * from pending_covered_trades where systemId = $systemId";
$result = $db->query($q);
$calc->db_error_test($result, $db, "25");
foreach ($result as $r) {
    $calc->send_signal($r);
}




echo $q = "select * from $optTable where profit_precent <= -40 and long_or_short = 'short'  ;";
$result = $db->query($q);
$number = mysqli_num_rows($result);
$calc->db_error_test($result, $db, "29");
echo $number . "\n";


if ($number > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "\n";
        sleep(5);
        $realOptionSymbol = $calc->eval_price($row);
        $table = strtolower($row['underlying'])."_options";
        echo $q= "select * from options2017.$table where symbol = '$realOptionSymbol';";
        echo "\n";
        $optionData = $db->query($q);
        while ($obj = $optionData->fetch_object()) {
            if ($obj->probability_ivol >=.50) {
                $transaction = "BTO";
                $condition = "stop";
                $strike = $row['strike'];

                // Option type
                if ($obj->opt_type == "put") {
                    $strike = $obj->strike;

                    //wierd options prices
                    // work in progress
                    if ($strike == 238) {
                        $cover_strike = 230;
                    } else {
                        $cover_strike = $strike-5;
                    }
                } else {
                    $strike = $obj->strike;
                    $cover_strike = $strike+5;
                }

                $cover_price_midPrice = ($obj->ask + $obj->bid)/2;
                $C2_option_symbol =  str_replace($row['strike'], $cover_strike, $row['symbol']);

                //Check to see if this particular symbol is part of
                echo   $q = "select symbol from $optTable where symbol = '$C2_option_symbol'";
                $optionData = $db->query($q);
                while ($object = $optionData->fetch_object()) {
                    if ($object->symbol == $C2_option_symbol) {
                        echo "we have a duplicate";
                        $dup = true;
                        sleep(10);
                    }
                }

                if ($dup != true) {
                    echo "Make trade\n";
                    //build signal array
                    $arr = json_encode(
                    array(
                      "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
                      "systemid" => $systemId,
                      "signal" => array(
                            "action" => "$transaction",
                            "symbol" => "$C2_option_symbol",
                            "typeofsymbol" => "option",
                            "$condition" => "$cover_price_midPrice",
                            "duration" => "DAY",
                            "quant" => $row['quant_opened']
                          )
                    )
                  );
                    //var_dump($arr);
                    $curl->setHeader('Content-Type', 'application/json');
                    $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
                    $response = $curl->response;
                    //var_dump($response);

                    //load trade into dump table
                    $tradeSignal =$response->signalid;
                    $calc->load_pending_covered_trades($tradeSignal, $systemTable, $systemId);
                } else {
                  echo "duplicate trade\n";
                }
            } else {
                echo "The probability is to low:".$obj->probability_ivol. "\n";
            }
        } //fetch object*/
    }
}
