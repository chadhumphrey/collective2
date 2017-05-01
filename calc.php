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

    public function get_mid_price($stock, $strike, $option)
    {
        global $db;
        $table = "options2017.".$stock."_options";
        $q ="SELECT midprice FROM $table where strike = $strike and opt_type = '$option' limit 1";
        $midprice = $db->query($q)->fetch_object()->midprice;
        _db_error_test($midprice, $db, "298");
        return $midprice -.10;
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
