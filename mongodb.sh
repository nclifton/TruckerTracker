#!/usr/bin/env bash

echo ">>> Installing MongoDB etc"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Get key and add to sources
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927

echo "deb http://repo.mongodb.org/apt/ubuntu trusty/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list

sudo add-apt-repository ppa:ondrej/php

# Update
sudo apt-get update

# will need the unzip command later
sudo apt-get install unzip

# Install MongoDB
# -qq implies -y --force-yes
sudo apt-get install -y --allow-unauthenticated mongodb-org

cat >/tmp/mongodb.service << EOL
[Unit]
Description=High-performance, schema-free document-oriented database
After=network.target

[Service]
User=mongodb
ExecStart=/usr/bin/mongod --quiet --config /etc/mongod.conf

[Install]
WantedBy=multi-user.target
EOL

sudo mv /tmp/mongodb.service /etc/systemd/system/

# create the data directory for mongodb
sudo mkdir -p /data/db
sudo chown mongodb:mongodb /data/db

echo 'provide new mongo.conf'
cp ${DIR}/mongod.conf /etc/mongod.conf

#start mongoDB
sudo systemctl start mongodb
sudo systemctl enable mongodb

# wait for listening message in log
tail -500f /var/log/mongodb/mongod.log | while read LOGLINE
do
    echo "${LOGLINE}"
   [[ "${LOGLINE}" == *"[initandlisten] waiting for connections on port 27017"* ]] && pkill -P $$ tail
done

#setup the trucker.tracker users
mongo admin ${DIR}/add_root.js
mongo admin ${DIR}/add_trucker_tracker.js

# add authentication now
sudo echo "security:" >> /etc/mongod.conf
sudo echo "  authorization: enabled" >> /etc/mongod.conf

#restart mongoDB
sudo systemctl restart mongodb

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

    echo "Fixing the pecl errors list";
    sudo sed -i -e 's/-C -n -q/-C -q/g' `which pecl`;

    echo "Installing OpenSSl Libraries";
    sudo apt-get install -y autoconf g++ make openssl libssl-dev libcurl4-openssl-dev;
    sudo apt-get install -y libcurl4-openssl-dev pkg-config;
    sudo apt-get install -y libsasl2-dev;

    # install php extension
    echo "no" > answers.txt
    sudo pecl install mongodb < answers.txt
    rm answers.txt

    # add extension file and restart service
    echo 'adding to php ini files, /etc/php/7.0/fpm/conf.d/20-mongodb.ini , /etc/php/7.0/cli/conf.d/20-mongodb.ini and /etc/php/7.0/mods-available/mongodb.ini'
    echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/fpm/conf.d/20-mongodb.ini && \
    echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/cli/conf.d/20-mongodb.ini && \
    echo 'extension=mongodb.so' | sudo tee /etc/php/7.0/mods-available/mongodb.ini

    echo "restarting The nginx server";
    sudo service nginx restart && sudo service php7.0-fpm restart
fi

