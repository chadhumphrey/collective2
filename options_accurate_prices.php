<?php
/**
**
This script will provide accurate options prices
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
$optTable = $systemTable."_opt";

$q = "select * from collective2.$optTable";
$result = $db->query($q);
$calc->db_error_test($result, $db, "25");

foreach ($result as $r) {
    $realOptionSymbol = $calc->eval_price($r);
    echo $realOptionSymbol . "\n";
    $table = strtolower($r['underlying'])."_options";
    $q= "select * from options2017.$table where symbol = '$realOptionSymbol';";
    $optionData = $db->query($q);
    while ($obj = $optionData->fetch_object()) {
      $midPrice = ($obj->ask + $obj->bid)/2;
    }
    $realOptionSymbol = $calc->update_price($r, $midPrice,$optTable);
}
