#!/bin/bash
start=$(date +"%T")

#send signals to systems
php /var/www/html/collective2/open_low_volatility_option_trades.php answer
php /var/www/html/collective2/open_low_volatility_option_trades.php entropy
#php /var/www/html/collective2/open_low_volatility_option_trades.php hardline
php /var/www/html/collective2/open_volatility_option_trades.php answer
php /var/www/html/collective2/open_volatility_option_trades.php entropy
#php /var/www/html/collective2/open_volatility_option_trades.php hardline

echo "Start time : $start"
now=$(date +"%T")
echo "Current time : $now"
