#!/bin/bash
#get symbols & values from Collective2
php /var/www/html/collective2/get_totals.php hardline
php /var/www/html/collective2/get_totals.php entropy
php /var/www/html/collective2/get_totals.php answer

#get symbols & values from Collective2
php /var/www/html/collective2/options_accurate_prices.php hardline
php /var/www/html/collective2/options_accurate_prices.php entropy
php /var/www/html/collective2/options_accurate_prices.php answer
