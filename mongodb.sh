#!/usr/bin/env bash

echo ">>> Installing MongoDB"

# Get key and add to sources
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10

echo "deb http://repo.mongodb.org/apt/ubuntu "$(lsb_release -sc)"/mongodb-org/3.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.0.list

sudo add-apt-repository ppa:ondrej/php

# Update
sudo apt-get update

# Install MongoDB
# -qq implies -y --force-yes
sudo apt-get install -y mongodb-org

# create the data directory for mongodb
sudo mkdir -p /data/db
sudo chown mongodb:mongodb /data/db

echo 'provide new mongo.conf'
cp /vagrant/mongod.conf /etc/mongod.conf

#start mongoDB
service mongod restart

# wait for listening message in log
tail -f /var/log/mongodb/mongod.log | while read LOGLINE
do
    echo "${LOGLINE}"
   [[ "${LOGLINE}" == *"[initandlisten] waiting for connections on port 27017"* ]] && pkill -P $$ tail
done

#setup the trucker.tracker mongo database
mongo admin /vagrant/trucker.tracker.js

# add authentication now
sudo echo "security:" >> /etc/mongod.conf
sudo echo "  authorization: enabled" >> /etc/mongod.conf

#restart mongoDB
service mongod restart

# wait for listening message in log
tail -f /var/log/mongodb/mongod.log | while read LOGLINE
do
    echo "${LOGLINE}"
   [[ "${LOGLINE}" == *"[initandlisten] waiting for connections on port 27017"* ]] && pkill -P $$ tail
done



# Test if PHP is installed ... so we install the php driver
php -v > /dev/null 2>&1
PHP_IS_INSTALLED=$?

if [ $PHP_IS_INSTALLED -eq 0 ]; then

    # install dependencies
    sudo apt-get -y install php7.0-fpm php7.0-mysql php7.0-curl php7.0-mcrypt php7.0-cli php7.0-dev php-pear libsasl2-dev

    sudo mkdir -p /usr/local/openssl/include/openssl/ && \
    sudo ln -s /usr/include/openssl/evp.h /usr/local/openssl/include/openssl/evp.h && \
    sudo mkdir -p /usr/local/openssl/lib/ && \
    sudo ln -s /usr/lib/x86_64-linux-gnu/libssl.a /usr/local/openssl/lib/libssl.a && \
    sudo ln -s /usr/lib/x86_64-linux-gnu/libssl.so /usr/local/openssl/lib/

    # install php extension
    echo "no" > answers.txt
    sudo pecl install mongodb < answers.txt
    rm answers.txt

    # add extension file and restart service
    echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/fpm/conf.d/20-mongodb.ini && \
    echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/cli/conf.d/20-mongodb.ini && \
    echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/mods-available/mongodb.ini

    sudo service php7.0-fpm restart
fi

