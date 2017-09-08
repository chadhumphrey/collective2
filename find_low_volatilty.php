<?php
/**
**
This script opens options tables and looks for Low IV and HV miss match.
**/
require_once("/var/www/html/stocks/constants.php");
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


$q = "truncate options_LowIVOL;";
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
        $q ='insert into options_LowIVOL () SELECT * FROM '.$table.'
        WHERE
        last != 0
        and ally_IVOL < HV_20D
        and strike between current_price - 50 and current_price +50
        and last > 2
        and last - ((bid+ ask)/2) >1
        ORDER BY
        ex_date,equity DESC';

        $rr = $db->query($q);
        $r = $calc->db_error_test($rr, $db, "49");
    }
}
