<?php
/**
**
This script will open a specific trade
@ Option Symbol, derivative stocks
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


$array_ids = array(
  array('stock'=>'amzn','opt_symbol'=>'AMZN171103P00950000','quantity'=>2),
  array('stock'=>'amzn','opt_symbol'=>'AMZN171027P00950000','quantity'=>2),
  array('stock'=>'incy','opt_symbol'=>'INCY171117P00110000','quantity'=>4),
);
$precent_increase = .95;
$precent_decrease = .90;
foreach ($array_ids as $a) {

    $table =  "options2017.".strtolower($a['stock'])."_options";

    $q = 'select * from '.$table.' where symbol = "'.$a['opt_symbol'].'"';
    $result = $db->query($q);
    $calc->db_error_test($result, $db, "29");

    foreach ($result as $r) {
        //log trade
        $date = date('Y-m-d', time());
        $q = 'insert into options2017.traded_option (symbol, date_of_quote) value ("'.$id.'", "'.$date.'" );';
        $insert = $db->query($q);
        $calc->db_error_test($insert, $db, "41");

        switch ($r['opt_type']) {
          case 'call':
            $action = "call";
            $condition = "stop";
            $transaction = "BTO";
            $exDate = $r['ex_date'];
            $target_price = (($r['bid']+$r['ask'])/2)*$precent_increase;
            break;
          case 'put':
            $strike =$calc->strike_type($r['strike']);
            $action = "put";
            $condition = "stop";
            $transaction = "BTO";
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
            "quant" => $a['quantity']
          ),
        )
      );
        var_dump(json_decode($arr));
        echo "\n\n\n\n";

        $curl->setHeader('Content-Type', 'application/json');
        $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
        $response = $curl->response;

        $insert_trade = build_insert($response, $systemTable);
        $result = $db->query($insert_trade);
        $calc->db_error_test($result, $db, "117");
        //var_dump($response)."\n";
        $tradeSignal =$response->signalid ;

        /*
        Build Conditional Cover trade
        */

        //build C2 options symbol
        $option_month_query =$calc->option_symbol_query($action, $d['month']);
        $day =$calc->add_zero_digit($d['day']);
        $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
        $cover_C2_option_symbol = strtoupper(trim($r['equity'])).$year.$day.$C2_option_month_symbol.$cover_strike;

        //build signal array
        $arr = json_encode(array(
          "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
          "systemid" => $systemId,
          "signal" => array(
                "action" => "$cover_transaction",
                "symbol" => "$cover_C2_option_symbol",
                "typeofsymbol" => "option",
                "$cover_condition" => "$cover_target_price",
                "duration" => "GTC",
                "quant" => 2,
                "conditionalupon" =>$tradeSignal
              ),
            )
          );
            // // var_dump(json_decode($arr));
            // echo "\n\n\n\n";

            $curl->setHeader('Content-Type', 'application/json');
        $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
        $response = $curl->response;
    } //foreach results
} //foreach array_ids
