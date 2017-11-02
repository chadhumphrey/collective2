<?php
/**
**
This script will open Options_VOL trades, but will also create cover for the open trade.
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

error_reporting(E_ALL);

echo $q = "select * from $optTable where profit_precent <= -39 and long_or_short = 'short' ;";
$result = $db->query($q);
$number = mysqli_num_rows($result);
$calc->db_error_test($result, $db, "29");

$xx =0;
if (($number) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // $date = date('Y-m-d', time());

        // switch ($row['opt_type']) {
        //   case 'call':
          $realOptionSymbol = $calc->eval_price($row);
          echo $realOptionSymbol . "\n";
          $table = strtolower($row['underlying'])."_options";
          echo $q= "select * from options2017.$table where symbol = '$realOptionSymbol';";
          $optionData = $db->query($q);
          while ($obj = $optionData->fetch_object()) {
            var_dump($obj);die;
            $midPrice = ($obj->ask + $obj->bid)/2;
            $ex_date = $obj->ex_date;
          }
          $realOptionSymbol = $calc->update_price($r,$ex_date, $midPrice,$optTable);
        }
      }

    /*        $strike =$calc->strike_type($r['strike']);
            echo $strike . "\n";
            $cover_strike = $strike+5;

            //Looking for decimal
            if (strpos($cover_strike, ".") == true) {
                $cover_strike = str_replace(".", "", $cover_strike);
            }
            if (strpos($strike, ".") == true) {
                $strike = str_replace(".", "", $strike);
            }

            $table =  "options2017.".strtolower($r['equity'])."_options";
            $xxx =  str_replace($strike, $cover_strike, $r['symbol']);
            echo "this cover_strike " .$xxx."\n";
            echo $q = 'select mid_price from '.$table.' where symbol = "'.$xxx.'"';
            $result = $db->query($q);
            if (!empty($result)) {
                $cover_target_price = $result->fetch_object()->mid_price;
                $cover_target_price = $precent_decrease * $cover_target_price;
            } else {
                echo "Fail line 55";
                die;
            }
                echo "this cover_strike " .$cover_target_price."\n";
                $cover_transaction = "BTO";
                $cover_condition="stop";

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

      }
    } // query while loop

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
            "quant" => 2
          ),
        )
      );
        // var_dump(json_decode($arr));
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

//         //build C2 options symbol
//         $option_month_query =$calc->option_symbol_query($action, $d['month']);
//         $day =$calc->add_zero_digit($d['day']);
//         $C2_option_month_symbol = $db->query($option_month_query)->fetch_object()->symbol;
//         $cover_C2_option_symbol = strtoupper(trim($r['equity'])).$year.$day.$C2_option_month_symbol.$cover_strike;
//
//         //build signal array
//         $arr = json_encode(array(
//           "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
//           "systemid" => $systemId,
//           "signal" => array(
//                 "action" => "$cover_transaction",
//                 "symbol" => "$cover_C2_option_symbol",
//                 "typeofsymbol" => "option",
//                 "$cover_condition" => "$cover_target_price",
//                 "duration" => "GTC",
//                 "quant" => 2,
//                 "conditionalupon" =>$tradeSignal
//               ),
//             )
//           );
//             // // var_dump(json_decode($arr));
//             // echo "\n\n\n\n";
//
//             $curl->setHeader('Content-Type', 'application/json');
//         $curl->post('https://api.collective2.com/world/apiv3/submitSignal', $arr);
//         $response = $curl->response;
//     } //foreach results
// } //foreach array_ids
