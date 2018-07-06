<?php

/* 10/9/17: simple load script to move the trade table up to the server.
*/

require '/var/www/html/vendor/autoload.php';
require_once("/var/www/html/stocks/constants.php");
include("/var/www/html/collective2/queries.php");

require_once("/var/www/html/collective2/calc.php");
$calc = new CALCULATION();
use \Curl\Curl;
$curl = new Curl();

error_reporting(E_ALL);

//New Mysqli Connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "collective2");
if (!$db) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}


//Send to the EC2 then import file into database
$ex = 'mysqldump -u root -pbenny collective2 C2_historical_trades > /var/www/html/stocks/version_two/portfolios/table_dump/C2_historical_trades.sql ';
echo exec($ex);

$ex = 'node /var/www/html/amazon/admin/send_table_to_web.js C2_historical_trades';
echo "\n" . $ex . "\n";
echo exec($ex);

$ex = 'cd /var/www/html/stocks/load_initial_stocks/ && php upload_table.php C2_historical_trades';
echo exec($ex);

//Send to the EC2 then import file into database
// $ex = 'mysqldump -u root -pbenny InteractiveB upcoming_earnings > /var/www/html/stocks/version_two/portfolios/table_dump/upcoming_earnings.sql ';
// echo exec($ex);
//
// $ex = 'node /var/www/html/amazon/admin/send_table_to_web.js upcoming_earnings';
// echo "\n" . $ex . "\n";
// echo exec($ex);
//
// $ex = 'cd /var/www/html/stocks/load_initial_stocks/ && php upload_table.php upcoming_earnings';
// echo exec($ex);

//clear historic trades
$result = $db->query('truncate C2_historical_trades');
$calc->db_error_test($result, $db, "25");
