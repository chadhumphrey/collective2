#!/bin/bash
start=$(date +"%T")

#Vol
php /var/www/html/collective2/php_scripts/volatility/find_high_volatility.php
php /var/www/html/collective2/php_scripts/volatility/find_low_volatilty.php 

echo "Start time : $start"
now=$(date +"%T")
echo "Current time : $now"
