#!/bin/bash
start=$(date +"%T")


#get accurate Quotes
#php /var/www/html/optionsV17/pull_ally_options.php

#get symbols & values from Collective2
php /var/www/html/collective2/get_totals.php hardline
php /var/www/html/collective2/get_totals.php entropy
php /var/www/html/collective2/get_totals.php answer

#get symbols & values from Collective2
php /var/www/html/collective2/options_accurate_prices.php hardline
php /var/www/html/collective2/options_accurate_prices.php entropy
php /var/www/html/collective2/options_accurate_prices.php answer

#set exit trades
php /var/www/html/collective2/set_option_trade_limits.php hardline

echo "Start time : $start"
now=$(date +"%T")
echo "Current time : $now"
