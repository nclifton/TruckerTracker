#!/usr/bin/env bash

# TODO

mkdir nginx
cd nginx

wget http://nginx.org/download/nginx-1.9.15.tar.gz
get clone https://github.com/slact/nchan.git

tar -xvf nginx-1.9.15.tar.gz
cd nginx-1.9.15/
./configure --add-module=../nchan \
--sbin-path=/usr/sbin/nginx \
--conf-path=/etc/nginx/nginx.conf \
--error-log-path=/var/log/nginx/error.log \
--http-log-path=/var/log/nginx/access.log \
--pid-path=/var/run/nginx.pid \
--lock-path=/var/run/nginx.lock \
--http-client-body-temp-path=/var/cache/nginx/client_temp \
--http-proxy-temp-path=/var/cache/nginx/proxy_temp \
--http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp \
--http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp \
--http-scgi-temp-path=/var/cache/nginx/scgi_temp \
--user=nginx \
--group=nginx \
--with-http_ssl_module \
--with-http_realip_module \
--with-http_addition_module \
--with-http_sub_module \
--with-http_dav_module \
--with-http_flv_module \
--with-http_mp4_module \
--with-http_gunzip_module \
--with-http_gzip_static_module \
--with-http_random_index_module \
--with-http_secure_link_module \
--with-http_stub_status_module \
--with-http_auth_request_module \
--with-threads \
--with-stream \
--with-stream_ssl_module \
--with-http_slice_module \
--with-mail \
--with-mail_ssl_module \
--with-file-aio \
--with-http_v2_module \
--with-ipv6

make

sudo make install

# configuration
# /etc/nginx/nginx.conf :-
# change "worker_processes auto;" to "worker_processes 5;"
#
sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.old
sudo sed -i -e 's/worker_processes auto;/worker_processes 5;/g' /etc/nginx/nginx.conf

sudo cp /etc/nginx/sites-available/homestead.app /etc/nginx/sites-available/homestead.app.orig
sudo cp /vagrant/nginx/homestead.app /etc/nginx/sites-available/homestead.app

sudo cp /vagrant/nginx/nginx.service /lib/systemd/system/nginx.service
sudo systemctl daemon-reload
sudo systemctl restart nginx.service
