#!/usr/bin/env bash
/bin/bash /vagrant/build/nginx_nchan.sh
/bin/bash /vagrant/build/mongodb.sh
/bin/bash /vagrant/build/mailcatcher.sh
echo "xdebug.remote_enable = 1" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.remote_connect_back = 1" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.remote_port = 9000" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.scream=0" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.cli_color=1" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.show_local_vars=1" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.idekey=phpstorm" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.max_nesting_level=300" >> /etc/php/7.0/mods-available/xdebug.ini
echo "error_log = /var/log/php7.0-fpm-error.log" >> /etc/php/7.0/fpm/php.ini
