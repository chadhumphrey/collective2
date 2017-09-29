#!/bin/bash
start=$(date +"%T")

#send signals to systems
php /var/www/html/collective2/php_scripts/trades/open_low_volatility_option_trades.php answer
php /var/www/html/collective2/php_scripts/trades/open_low_volatility_option_trades.php entropy
php /var/www/html/collective2/php_scripts/trades/open_low_volatility_option_trades.php hardline
php /var/www/html/collective2/php_scripts/trades/open_high_volatility_option_trades.php answer
php /var/www/html/collective2/php_scripts/trades/open_high_volatility_option_trades.php entropy
php /var/www/html/collective2/php_scripts/trades/open_high_volatility_option_trades.php hardline

#Covered trades
php /var/www/html/collective2/php_scripts/trades/open_high_vol_cover_trades.php hardline
php /var/www/html/collective2/php_scripts/trades/open_high_vol_cover_trades.php entropy
php /var/www/html/collective2/php_scripts/trades/open_high_vol_cover_trades.php answer

echo "Start time : $start"
now=$(date +"%T")
echo "Current time : $now"
