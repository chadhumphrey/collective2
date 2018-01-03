
#!/bin/bash
start=$(date +"%T")

#get accurate Quotes
php /var/www/html/optionsV17/pull_c2_options.php

#get symbols & values from Collective2
php /var/www/html/collective2/php_scripts/prices/get_totals.php hardline
php /var/www/html/collective2/php_scripts/prices/get_totals.php entropy
php /var/www/html/collective2/php_scripts/prices/get_totals.php answer

#get symbols & values from Collective2
php /var/www/html/collective2/php_scripts/prices/options_accurate_prices.php hardline
php /var/www/html/collective2/php_scripts/prices/options_accurate_prices.php entropy
php /var/www/html/collective2/php_scripts/prices/options_accurate_prices.php answer

#get margins and accounts balances of systems
php /var/www/html/collective2/php_scripts/utility/get_margin.php
php /var/www/html/collective2/php_scripts/utility/load_trades.php
php /var/www/html/collective2/php_scripts/utility/load_trades_IB.php

#set exit trades
php /var/www/html/collective2/php_scripts/trades/set_option_trade_limits.php hardline
php /var/www/html/collective2/php_scripts/trades/set_option_trade_limits.php entropy
php /var/www/html/collective2/php_scripts/trades/set_option_trade_limits.php answer

#set rebalance/cover trades
php /var/www/html/collective2/php_scripts/trades/open_rebalance_trade.php hardline
php /var/www/html/collective2/php_scripts/trades/open_rebalance_trade.php entropy
php /var/www/html/collective2/php_scripts/trades/open_rebalance_trade.php answer


echo "Start time : $start"
now=$(date +"%T")
echo "Current time : $now"
