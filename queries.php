<?php

$truncate_trades="truncate trades;";

function build_query($r)
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

$truncate_local_stocks= "TRUNCATE local_stocks;";
$truncate_opt= "TRUNCATE opt;";

$move_to_local= "INSERT INTO local_stocks (
	symbol,
	symbol_description,
	long_or_short,
	PL,
	quant_opened,
	trade_id,
	position_open_date,
	purchase_price
  ) SELECT
	symbol,
	symbol_description,
	long_or_short,
	PL,
	quant_opened,
	trade_id,
	FROM_UNIXTIME(
		closeVWAP_timestamp,
		'%Y-%m-%d'
	),
	opening_price_VWAP
  FROM
	`trades`
  WHERE
	open_or_closed != 'closed'
  and putcall = '';";

  $move_to_options= "INSERT INTO `opt` (
	closeVWAP_timestamp,
	strike,
	open_or_closed,
	expir,
	openVWAP_timestamp,
	underlying,
	closing_price_VWAP,
	putcall,
	quant_closed,
	markToMarket_time ,
	opening_price_VWAP,
	trade_id,
	symbol,
	quant_opened,
	closedWhen,
	instrument,
	ptValue,
	PL,
	closedWhenUnixTimeStamp,
	openedWhen,
	long_or_short,
	symbol_description
) SELECT
	closeVWAP_timestamp,
	strike,
	open_or_closed,
	expir,
	openVWAP_timestamp,
	underlying,
	closing_price_VWAP,
	putcall,
	quant_closed,
	markToMarket_time ,
	opening_price_VWAP,
	trade_id,
	symbol,
	quant_opened,
	closedWhen,
	instrument,
	ptValue,
	PL,
	closedWhenUnixTimeStamp,
	openedWhen ,
	long_or_short,
	symbol_description
FROM
	trades
WHERE
	 putcall != '';";

$update_algo= "UPDATE collective2.local_stocks AS c2
  INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = c2.symbol
  SET c2.SO_algo_action = stocks_bluesky.all_stocks_alt.action
  WHERE
  stocks_bluesky.all_stocks_alt.date_results = (
  SELECT
  max(date_results)
  FROM
  stocks_bluesky.all_stocks_alt);";

  $update_algo_options = "UPDATE collective2.opt AS c2
  INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = LOWER(c2.underlying)
  SET c2.SO_algo_action = stocks_bluesky.all_stocks_alt.action
  WHERE
  stocks_bluesky.all_stocks_alt.date_results = (
  SELECT
  max(date_results)
  FROM
  stocks_bluesky.all_stocks_alt);";

$update_price = "UPDATE collective2.local_stocks AS c2
  INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = c2.symbol
	set c2.stock_price = stocks_bluesky.all_stocks_alt.close_results
  WHERE
	stocks_bluesky.all_stocks_alt.date_results = (
	SELECT
	max(date_results)
	FROM
	stocks_bluesky.all_stocks_alt); ";

  $update_profit_long = "update local_stocks set percentage_PL = ((stock_price - purchase_price) / purchase_price)*100 where long_or_short = 'long';";
  $update_profit_short = "update local_stocks set percentage_PL = ((purchase_price - stock_price) / purchase_price)*100  where long_or_short = 'short';";
  $update_trade_duration = "UPDATE local_stocks SET trade_duration = DATEDIFF(CURRENT_DATE (), position_open_date ); ";

  $update_long_delta = " update `local_stocks` set delta = (quant_opened/100) where long_or_short = 'long'";
  $update_short_delta = " update `local_stocks` set delta = (quant_opened/100) *-1 where long_or_short = 'short'";

  $update_long_trigger = "UPDATE collective2.local_stocks AS c2
    INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = c2.symbol
    SET c2.exit_trigger = stocks_bluesky.all_stocks_alt.girt_mn_sell
    WHERE
    	stocks_bluesky.all_stocks_alt.date_results = (
    		SELECT
    			max(date_results)
    		FROM
    			stocks_bluesky.all_stocks_alt
    	)
    AND c2.long_or_short = 'long';";

  $update_short_trigger = "UPDATE collective2.local_stocks AS c2
    INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = c2.symbol
    SET c2.exit_trigger = stocks_bluesky.all_stocks_alt.girt_mn_buy
    WHERE
    	stocks_bluesky.all_stocks_alt.date_results = (
    		SELECT
    			max(date_results)
    		FROM
    			stocks_bluesky.all_stocks_alt
    	)
    AND c2.long_or_short = 'short';";

  //Set exit points for stocks in portfolio that are out of sync
  $set_safety_trades = "SELECT
	symbol,
	close_results,
	long_or_short,
	quant_opened,
	trade_id,
	stocks_bluesky.all_stocks_alt.girt_mn_buy,
	stocks_bluesky.all_stocks_alt.girt_mn_sell
  FROM
	`local_stocks`
  INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = local_stocks.symbol
  WHERE stocks_bluesky.all_stocks_alt.date_results = CURRENT_DATE
  AND long_or_short = 'long'
  AND SO_algo_action = 'sell'
  UNION
	SELECT
		symbol,
		close_results,
		long_or_short,
		quant_opened,
		trade_id,
		stocks_bluesky.all_stocks_alt.girt_mn_buy,
		stocks_bluesky.all_stocks_alt.girt_mn_sell
	FROM
		`local_stocks`
	INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = local_stocks.symbol
	WHERE
		stocks_bluesky.all_stocks_alt.date_results = CURRENT_DATE
	AND long_or_short = 'short'
	AND SO_algo_action = 'buy'" ;

  //Set exit points for stocks in portfolio t
  $set_all_daily_trade_trigger = "SELECT
	symbol,
	close_results,
	long_or_short,
	quant_opened,
	trade_id,
	stocks_bluesky.all_stocks_alt.girt_mn_buy,
	stocks_bluesky.all_stocks_alt.girt_mn_sell
  FROM
	`local_stocks`
  INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = local_stocks.symbol
  WHERE stocks_bluesky.all_stocks_alt.date_results = CURRENT_DATE;";

  $open_option_trades = "(SELECT
	std,
	20DMA_STD,
	calc_equity,
	close_results,
	final_neo_score,
	trade_duration,action
  FROM
	stocks_bluesky.all_stocks_alt
  WHERE
	trade_duration between 1 and 2
  AND final_neo_score>0
  AND date_results = CURRENT_DATE
  AND std > 20DMA_STD
  limit 5)
UNION
(SELECT
	std,
	20DMA_STD,
	calc_equity,
	close_results,
	final_neo_score,
	trade_duration,action
  FROM
	stocks_bluesky.all_stocks_alt
  WHERE
	trade_duration between 1 and 2
  AND final_neo_score < -1
  AND date_results = CURRENT_DATE
  AND std > 20DMA_STD limit 5);";

  function _option_symbol_query($act, $month)
  {
      $query = "select `$act` as symbol from options_symbols where id = $month";
      return $query;
  }

  function _get_strike($price, $action)
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

  function _get_mid_price($stock,$strike,$option){
    global $db;
    $table = "options2017.".$stock."_options";
    echo $q ="SELECT midprice FROM $table where strike = $strike and opt_type = '$option' limit 1";
    $midprice = $db->query($q)->fetch_object()->midprice;
    _db_error_test($midprice, $db, "298");
    return $midprice;
  }


  function _db_error_test($results, $db, $line = null)
  {
      if (!$results) {
          echo "ERROR:send_signals.php" . "\n";
          echo "Line Number " . $line . "\n";
          echo mysqli_error($db) . "\n";
          die;
      }
  }
