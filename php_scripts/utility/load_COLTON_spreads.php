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
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "InteractiveColton");
if (!$db) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}


//Send to the EC2 then import file into database
$ex = 'mysqldump -u root -pbenny InteractiveColton calc_spreads_table  > /var/www/html/stocks/version_two/portfolios/table_dump/calc_spreads_tableCOLTON.sql ';
echo exec($ex);

$ex = "sed -i 's/`calc_spreads_table`/`calc_spreads_tableCOLTON`/g' /var/www/html/stocks/version_two/portfolios/table_dump/calc_spreads_tableCOLTON.sql ";
echo exec($ex);

$ex = 'node /var/www/html/amazon/admin/send_table_to_web.js calc_spreads_tableCOLTON';
echo "\n" . $ex . "\n";
echo exec($ex);

$ex = 'cd /var/www/html/stocks/load_initial_stocks/ && php upload_table.php calc_spreads_tableCOLTON';
echo exec($ex);
