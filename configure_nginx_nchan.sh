#!/usr/bin/env bash

# configuration
# /etc/nginx/nginx.conf :-
# change "worker_processes auto;" to "worker_processes 5;"
#
sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.old
sudo sed -i -e 's/worker_processes auto;/worker_processes 5;/g' /etc/nginx/nginx.conf

sudo cp /etc/nginx/sites-available/homestead.app /etc/nginx/sites-available/homestead.app.orig
sudo cp /vagrant/nginx/homestead.app /etc/nginx/sites-available/homestead.app

# sudo cp /vagrant/nginx/nginx.service /lib/systemd/system/nginx.service
# sudo systemctl daemon-reload
# sudo systemctl restart nginx.service
