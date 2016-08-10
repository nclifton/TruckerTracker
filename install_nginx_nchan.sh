#!/usr/bin/env bash

# TODO

mkdir nginx
cd nginx

wget https://nchan.slact.net/download/nginx-common.ubuntu.deb
wget https://nchan.slact.net/download/nginx-extras.ubuntu.deb

sudo apt-get remove nginx-full

sudo dpkg -i nginx-common.ubuntu.deb
sudo apt-get install liblua5.1-0
sudo dpkg -i nginx-extras.ubuntu.deb
sudo apt-get -f install

