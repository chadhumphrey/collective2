<?php
error_reporting(0);
class CALCULATION
{
    public function build_query($r)
    {
        $q = 'insert into trades (
          closeVWAP_timestamp,
          strike ,
          open_or_closed ,
          expir ,
          openVWAP_timestamp ,
          underlying ,
          closing_price_VWAP ,
          putcall ,
          quant_closed ,
          markToMarket_time,
          opening_price_VWAP ,
          trade_id ,
          symbol ,
          quant_opened ,
          closedWhen ,
          instrument ,
          ptValue ,
          PL,
          closedWhenUnixTimeStamp,
          openedWhen ,
          long_or_short ,
          symbol_description
          ) values (
          "'.$r->closeVWAP_timestamp.'",
          "'.$r->strike.'",
          "'.$r->open_or_closed.'",
          "'.$r->expir.'",
          "'.$r->openVWAP_timestamp.'",
          "'.$r->underlying.'",
          "'.$r->closing_price_VWAP.'",
          "'.$r->putcall.'",
          "'.$r->quant_closed.'",
          "'.$r->markToMarket_time.'",
          "'.$r->opening_price_VWAP.'",
          "'.$r->trade_id.'",
          "'.$r->symbol.'",
          "'.$r->quant_opened.'",
          "'.$r->closedWhen.'",
          "'.$r->instrument.'",
          "'.$r->ptValue.'",
          "'.$r->PL.'",
          "'.$r->closedWhenUnixTimeStamp.'",
          "'.$r->openedWhen.'",
          "'.$r->long_or_short.'",
          "'.$r->symbol_description.'")';
        return $q;
    }

    public function option_symbol_query($act, $month)
    {
        $query = "select `$act` as symbol from options_symbols where id = $month";
        return $query;
    }

    public function add_zero_digit($day)
    {
        if ($day<10) {
            $d = "0".$day;
            return $d;
        }
        return $day;
    }

    public function get_strike($price, $action)
    {
        if ($action == "call") {
            $variable = (int)$price;
            $variable += (-5 - ($variable % 5)) % 5;
        } else {
            $variable = (int)$price;
            $variable += (5 - ($variable % 5)) % 5;
        }
        return $variable;
    }

    public function validate_expiration($stock, $strike, $option, $current_expiration, $secondary_expiration)
    {
        global $db;
        $table = "options2017.".$stock."_options";
        $q ='SELECT * FROM '.$table.' where strike = '.$strike.' and opt_type = "'.$option.'" and ex_date = "'.$current_expiration.'"  limit 1';
        $result = $db->query($q);
        if (mysqli_num_rows($result) > 0) {
            return $current_expiration;
        } else {
            $q2 ='SELECT * FROM '.$table.' where strike = '.$strike.' and opt_type = "'.$option.'" and ex_date = "'.$secondary_expiration.'"  limit 1';
            $result2 = $db->query($q2);
            if (mysqli_num_rows($result2) > 0) {
                return $secondary_expiration;
            } else {
                return "2018-01-19";
            }
        }
    }

    public function get_mid_price($stock, $strike, $option, $exDate)
    {
        global $db;
        $table = "options2017.".$stock."_options";
        $q ="SELECT midprice FROM $table where strike = $strike and opt_type = '$option' and ex_date = '$exDate' limit 1";
        $result = $db->query($q);
        if (!empty($result)) {
            $midprice = $result->fetch_object()->midprice;
            if ($midprice <= 0) {
                $q ="SELECT last FROM $table where strike = $strike and opt_type = '$option' and ex_date = '$exDate' limit 1";
                echo $q . " <-----\n";
                $result = $db->query($q);
                $last_price = $result->fetch_object()->last;
                return $last_price - .10;
            } else {
                return $midprice -.10;
            }
        } else {
            return null;
        }
    }

    public function strike_type($strike)
    {
        if (strpos($strike, ".")) {
            $remainder = substr($strike, strpos($strike, ".") + 1);
            if ($remainder == "50") {
                $strike = substr($strike, 0, -1);
                return $strike;
            } else {
                $strike = str_replace(".00", "", $strike);
                return $strike;
            }
        }
    }

    public function eval_price($data)
    {
        global $db;
        switch ($data['putcall']) {
          case 'call':
            $option = "C";
            $opt = "call";
            break;
          case 'put':
            $option = "P";
            $opt = "put";
            break;
          default:
              $opt =$option = "FAIL";
            break;
        }

        $x = str_replace($data['underlying'], "", $data['symbol']);
        $year = substr($x, 0, 2);
        $day = substr($x, 2, 2);
        $tempMonth = $x[4];

        $q="select id from collective2.options_symbols where `$opt` = '$tempMonth';";
        $month = $db->query($q)->fetch_object()->id;
        (float)$data['strike'];

        //Single digit months
        if (strlen($month)<2) {
            echo "single digit\n";
            $month = sprintf("%02d", $month);
        }

        $s = (float)$data['strike'] ;

        //if the strike has a decimal
        if (fmod((float)$data['strike'], 1)) {
            $strike =(float)$data['strike'] *10;
            echo "strikes! ". $strike . "\n";
            if (strlen($strike)==3) {
                $strike = "000".$data['strike']."00";
                $strike = str_replace(".", "", $strike);
                $realOptionSymbol = $data['underlying'].$year.$month.$day.$option.$strike;
                echo $realOptionSymbol . "\n";
                return $realOptionSymbol;
            } else {
                $strike = "00".$data['strike']."00";
                $strike = str_replace(".", "", $strike);
                $realOptionSymbol = $data['underlying'].$year.$month.$day.$option.$strike;
                echo $realOptionSymbol . "\n";
                return $realOptionSymbol;
            }
        }


        if (strlen($data['strike'])==2) {
            $strike = "000".$data['strike']."000";
        }
        if (strlen($data['strike'])==3) {
            $strike = "00".$data['strike']."000";
        }
        echo "Strike---->".$strike . "\n";
        echo "Day---->".$day . "\n";
        // die;
        //$option = "C";
        //BMY170728C00080000

        $realOptionSymbol = $data['underlying'].$year.$month.$day.$option.$strike;
        echo $realOptionSymbol . "\n";
        return $realOptionSymbol;
    }

    public function update_price($r, $ex_date,$midPrice, $optTable)
    {
        global $db;
        echo "midPrice--> ". $midPrice . "\n";
        echo "start price --> ". $r['opening_price_VWAP'] . "\n";
        if ($r['long_or_short']=="long") {
            $profit_precent = (($midPrice - $r['opening_price_VWAP']) / $r['opening_price_VWAP']) *100;
            $profit = ($midPrice - $r['opening_price_VWAP']) *100 * $r['quant_opened'];
        } else {
            $profit_precent = (($r['opening_price_VWAP'] -$midPrice)   / $r['opening_price_VWAP']) *100;
            $profit = ($r['opening_price_VWAP']- $midPrice) *100 * $r['quant_opened'];
        }
        echo "% profit--> ". $profit . "\n";

        $q = "update $optTable set actual_midPrice = $midPrice, profit_precent = $profit_precent, profit = $profit, ex_date = \"$ex_date\" where id = $r[id];";
        $result = $db->query($q);
        $this->db_error_test($result, $db, "214");
    }

    public function get_system($systemName)
    {
        switch ($systemName) {
          case 'hardline':
            $systemId = "109963544";
            break;
          case 'generator':
            $systemId = "109963544";
            break;
          case 'entropy':
            $systemId = "113460016";
            break;
          case 'answer':
            $systemId = "113494319";
            break;
          default:
            die("line 228 on calc.php- Meaning you didn't put in a system\n");
        }
        return $systemId;
    }

    public function get_system_table($systemName)
    {
        switch ($systemName) {
        case 'hardline':
          $table = "hardline";
          break;
        case 'entropy':
          $table = "entropy";
          break;
        case 'answer':
          $table = "answer";
          break;
        default:
          die("line 243 on calc.php");
      }
        return $table;
    }


    public function build_c2_options($system_array)
    {
        global $db;
        foreach ($system_array as $system) {
            $systemTable = $this->get_system_table($system);
            $optTable = "collective2.".$systemTable."_opt";
            $q = "select underlying from $optTable";
            $result = mysqli_query($db, $q);
            $this->db_error_test($result, $db, "pull_optionsV17 40");
            $number = mysqli_num_rows($result);
            if (($number) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $stock = $row['underlying'];
                    $qq = 'insert ignore into DAY_TRADING_options (equity) value ("'.strtolower(trim($stock)).'")';
                    $results = mysqli_query($db, $qq);
                    $this->db_error_test($result, $db, "pull_c2_options 47");
                }
            }
        }
    }

    public function build_IB_options()
    {
        global $db;
        foreach ($system_array as $system) {
            $systemTable = $this->get_system_table($system);
            $optTable = "InteractiveB.IB_opt";
            $q = "select symbol from $optTable";
            $result = mysqli_query($db, $q);
            $this->db_error_test($result, $db, "calc 297");
            $number = mysqli_num_rows($result);
            if (($number) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $stock = $row['symbol'];
                    $qq = 'insert ignore into DAY_TRADING_options (equity) value ("'.strtolower(trim($stock)).'")';
                    $results = mysqli_query($db, $qq);
                    $this->db_error_test($result, $db, "pull_c2_options 47");
                }
            }
        }
    }

    public function send_signal($r)
    {
        global $curl;
        global $db;
        $systemId=$r['systemId'];
        $signalId=$r['signalId'];

        $arr = json_encode(
          array(
          "apikey" => "HGHo2JKR2akIJdWtPRZU_LCLrYXAanVOgLLdoDOw28NcGr_v5e",
          "systemid" => $systemId,
          "signalid" => $signalId
        )
        );

        $curl->setHeader('Content-Type', 'application/json');
        $curl->post('https://api.collective2.com/world/apiv3/cancelSignal', $arr);
        $response = $curl->response;

        var_dump(($response));
        echo "\n\n\n\n";

        //delete from tables
        $q = "delete from pending_trades where signalId  = $signalId";
        $result = $db->query($q);
        $this->db_error_test($result, $db, "calc 314");
    }

    public function load_pending_trades($tradeSignal, $systemName, $systemId)
    {
        global $db;
        $q = "insert into pending_trades (signalId,systemName,systemId) values ('.$tradeSignal.',\"$systemName\",'.$systemId.')";
        $result = $db->query($q);
        $this->db_error_test($result, $db, "calc 322");
    }

    public function load_pending_covered_trades($tradeSignal, $systemName, $systemId)
    {
        global $db;
        $q = "insert into pending_covered_trades (signalId,systemName,systemId) values ('.$tradeSignal.',\"$systemName\",'.$systemId.')";
        $result = $db->query($q);
        $this->db_error_test($result, $db, "calc 330");
    }

    public function db_error_test($results, $db, $line = null)
    {
      global $db;
        if (!$results) {
            echo "ERROR:calc.php" . "\n";
            echo "Line Number " . $line . "\n";
            echo mysqli_error($db) . "\n";
            return "FAIL";
            die;
        }
    }
} //end of class
