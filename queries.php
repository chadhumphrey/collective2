<?php

$truncate_trades="truncate trades;";

function build_query($r){ $q = 'insert into trades (
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

$update_algo= "UPDATE collective2.local_stocks AS c2
  INNER JOIN stocks_bluesky.all_stocks_alt ON stocks_bluesky.all_stocks_alt.calc_equity = c2.symbol
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
