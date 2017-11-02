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
    echo $realOptionSymbol . "\n";die;
    $table = strtolower($r['underlying'])."_options";
    echo $q= "select * from options2017.$table where symbol = '$realOptionSymbol';";die;
    $optionData = $db->query($q);
    while ($obj = $optionData->fetch_object()) {
        $midPrice = ($obj->ask + $obj->bid)/2;
        $ex_date = $obj->ex_date;
        echo"ldafsjlsjdf". $ex_date ; die;
    }
    $realOptionSymbol = $calc->update_price($r,$ex_date, $midPrice, $optTable);
}

//Send to the EC2 then import file into database
$ex = 'mysqldump -u root -pbenny collective2 '.$systemTable.' > /var/www/html/stocks/version_two/portfolios/table_dump/'.$systemTable.'.sql ';
$ex2 = 'mysqldump -u root -pbenny collective2 '.$optTable.' >    /var/www/html/stocks/version_two/portfolios/table_dump/'.$optTable.'.sql';
echo exec($ex);
echo exec($ex2);

$ex = 'node /var/www/html/amazon/admin/send_table_to_web.js '.$systemTable.'';
    echo "\n" . $ex . "\n";
    echo exec($ex);
$ex = 'node /var/www/html/amazon/admin/send_table_to_web.js '.$optTable.'';
    echo "\n" . $ex . "\n";
    echo exec($ex);

$ex = 'cd /var/www/html/stocks/load_initial_stocks/ && php upload_table.php '.$systemTable.'';
    echo exec($ex);

$ex = 'cd /var/www/html/stocks/load_initial_stocks/ && php upload_table.php '.$optTable.'';
    echo exec($ex);

  $date = date('Y-m-d H:i:s');
  echo "The time is: ".$date = date('Y-m-d H:i:s') . "\n";
