<?php

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
        echo "-----> ". $q . " <-----";
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

    public function get_mid_price($stock, $strike, $option,$exDate)
    {
        global $db;
        $table = "options2017.".$stock."_options";
        $q ="SELECT midprice FROM $table where strike = $strike and opt_type = '$option' and ex_date = '$exDate' limit 1";
        echo $q . " <-----\n";
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


    public function db_error_test($results, $db, $line = null)
    {
        if (!$results) {
            echo "ERROR:send_signals.php" . "\n";
            echo "Line Number " . $line . "\n";
            echo mysqli_error($db) . "\n";
            die;
        }
    }
} //end of class
