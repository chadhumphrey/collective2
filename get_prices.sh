#!/bin/bash

php /var/www/html/collective2/get_totals.php
node /var/www/html/amazon/admin/send_C2_toEC2.js

php /var/www/stock_BlueSky/version_two/daily_calculations.php



#php /var/www/options/pull_optionsV2.php
echo "2 minute warning"
sleep 2m
