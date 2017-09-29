<?php

/* 9/21/17: simple script to get margin equity and balances
parameter: needs systemTable, but that is now in an array at the top
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

$system_array = array('hardline','entropy','answer');
$q= "truncate accounts;";
$result = $db->query($q);
$calc->db_error_test($result, $db, "30");

foreach ($system_array as $system) {
echo $system . "\n";
//Get user input on tables
    $systemId = $calc->get_system($system);
    $systemTable = $calc->get_system_table($system);
    $arr = array(
      'apikey' => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
      'systemid' => $systemId
    );

$curl->get('https://api.collective2.com/world/apiv3/requestMarginEquity', $arr);
    if ($curl->error) {
        echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
    } else {
        $response = $curl->response;
        $insert_margin = build_margin_query($response, $systemTable);
        $result = $db->query($insert_margin);
        $calc->db_error_test($result, $db, "56");
    }
}

//Send to the EC2 then import file into database
$ex = 'mysqldump -u root -pbenny collective2 accounts > /var/www/html/stocks/version_two/portfolios/table_dump/accounts.sql ';
echo exec($ex);

$ex = 'node /var/www/html/amazon/admin/send_table_to_web.js accounts';
echo "\n" . $ex . "\n";
echo exec($ex);

$ex = 'cd /var/www/html/stocks/load_initial_stocks/ && php upload_table.php accounts';
echo exec($ex);
