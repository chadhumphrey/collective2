<?php
/**
**
This script open options trades, from Options_VOL table. Focus is on miss placed volatility
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

//Get options one month in advance.
$options_array = array(
array('INCY180615C00125000',2),
array('TQQQ180615P00106000',2),
array('TNA180720P00053000',2),
array('SPY180928P00233000',2),
array('ADS180615P00220000',2),
);
$precent_increase = 1.05;
foreach ($options_array as $val) {
    $q = 'select * from options2017.OPTIONS_HighIVOL where symbol = "'.$val[0].'"';
    $result = $db->query($q);
    $calc->db_error_test($result, $db, "29");

    foreach ($result as $r) {
        //log trade
        $date = date('Y-m-d', time());
        echo $q = 'insert into options2017.traded_option (symbol, date_of_quote) value ("'.$val[0].'", "'.$date.'" );';
        $insert = $db->query($q);
        $calc->db_error_test($insert, $db, "41");

        switch ($r['opt_type']) {
      case 'call':
        $strike =$calc->strike_type($r['strike']);
        $action = "call";
        $condition = "limit";
        $transaction = "STO";
        $exDate = $r['ex_date'];
        $target_price = (($r['bid']+$r['ask'])/2)*$precent_increase;
        break;
      case 'put':
        $strike =$calc->strike_type($r['strike']);
        $action = "put";
        $condition = "limit";
        $transaction = "STO";
        $exDate = $r['ex_date'];
        $target_price = (($r['bid']+$r['ask'])/2)*$precent_increase;
        break;
      default:
        break;
  }


    //Parse Expiration Date
    $d = date_parse($exDate);
        $year = date('y', strtotime($exDate));



    //build C2 options symbol
    $option_month_query =$calc->option_symbol_query($action, $d['month']);
        $day =$calc->add_zero_digit($d['day']);
        $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
        $C2_option_symbol = strtoupper(trim($r['equity'])).$year.$day.$C2_option_month_symbol.$strike;

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
            "quant" => $val[1]
          ),
        )
      );
        //var_dump(json_decode($arr));
        echo "\n\n\n\n";

        $curl->setHeader('Content-Type', 'application/json');
        $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
        $response = $curl->response;
         var_dump($response)."\n";
    } //foreach results
} //foreach array_ids
