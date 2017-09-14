<?php
/**
**
This script queries systems tables (hardline, answer, entropy) to check accurate prices
-Based on accurate prices it sets trades
**/
require '/var/www/html/vendor/autoload.php';
require_once("/var/www/html/stocks/constants.php");
include("queries.php");
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

//Get user input on tables
$systemTable = $calc->get_system_table($argv[1]);
$systemId = $calc->get_system($argv[1]);

$optTable = $systemTable."_opt";

$q = "select * from collective2.$optTable";
$result = $db->query($q);
$calc->db_error_test($result, $db, "25");

$precent_increase = .90;
$precent_decrease = .90;

foreach ($result as $r) {
    if ($r['profit_precent'] >= 60) {
        echo "profit ---> ".$r['profit'] . "\n";
        $transaction = "BTO";
        if ($r['long_or_short']=='short') {
            $transaction = "BTC";
            $condition = "limit";
            $target_price = $r['actual_midPrice']*$precent_decrease;
        } else {
            $transaction = "STO";
            $condition = "limit";
            $target_price = $r['actual_midPrice']*$precent_decrease;
        }
        $C2_option_symbol = $r['symbol'];

      //build signal array
      $arr = json_encode(array(
        "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
        "systemid" => $systemId,
        "signal" => array(
              "action" => "$transaction",
              "symbol" => "$C2_option_symbol",
              "typeofsymbol" => "option",
              "$condition" => "$target_price",
              "duration" => "DAY",
              "quant" => $r['quant_opened']
            ),
          )
        );
        var_dump(json_decode($arr));
        echo "\n\n\n\n";

        $curl->setHeader('Content-Type', 'application/json');
        $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
        $response = $curl->response;
        var_dump($response)."\n";
    } //foreach results
} //foreach array_ids
