#!/usr/bin/env bash

# TODO


echo "installing nginx nchan"

ls -l /etc/nginx/sites-available/

cd /home/vagrant/truckertracker/nginx

wget -nv https://github.com/slact/nchan/archive/v1.0.3.tar.gz -O nchan-v1.0.3.tar.gz
tar -zxf nchan-v1.0.3.tar.gz

wget -nv http://nginx.org/download/nginx-1.11.3.tar.gz
tar -zxf nginx-1.11.3.tar.gz

wget -nv https://github.com/stogh/ngx_http_auth_pam_module/archive/v1.5.1.tar.gz -O ngx_http_auth_pam_module-v1.5.1.tar.gz
tar -zxf ngx_http_auth_pam_module-v1.5.1.tar.gz

wget -nv https://github.com/arut/nginx-dav-ext-module/archive/v0.0.3.tar.gz -O nginx-dav-ext-module-v0.0.3.tar.gz
tar -zxf nginx-dav-ext-module-v0.0.3.tar.gz

wget -nv https://github.com/openresty/echo-nginx-module/archive/v0.60.tar.gz -O echo-nginx-module-v0.60.tar.gz
tar -zxf echo-nginx-module-v0.60.tar.gz

wget -nv https://github.com/gnosek/nginx-upstream-fair/tarball/master -O nginx-upstream-fair.tar.gz
tar -zxf nginx-upstream-fair.tar.gz

wget -nv https://github.com/yaoweibin/ngx_http_substitutions_filter_module/archive/v0.6.4.tar.gz -O ngx_http_substitutions_filter_module-v0.6.4.tar.gz
tar -zxf ngx_http_substitutions_filter_module-v0.6.4.tar.gz

echo "installing nginx nchan dependancies"

cp /home/vagrant/truckertracker/build/apt-get.sh /usr/local/sbin/apt-get
sudo chmod +x /usr/local/sbin/apt-get
sudo /usr/local/sbin/apt-get -y update
sudo /usr/local/sbin/apt-get install -y libxslt-dev libgd-dev libgeoip-dev libpam-dev

echo "*** downloads done configuring for make"

cd nginx-1.11.3

./configure \
--with-cc-opt='-g -O2 -fPIC -fstack-protector-strong -Wformat -Werror=format-security -Wdate-time -D_FORTIFY_SOURCE=2' \
--with-ld-opt='-Wl,-Bsymbolic-functions -fPIE -pie -Wl,-z,relro -Wl,-z,now' \
--prefix=/etc/nginx \
--conf-path=/etc/nginx/nginx.conf \
--sbin-path=/usr/sbin/nginx \
--http-log-path=/var/log/nginx/access.log \
--error-log-path=/var/log/nginx/error.log \
--lock-path=/var/lock/nginx.lock \
--pid-path=/run/nginx.pid \
--http-client-body-temp-path=/var/lib/nginx/body \
--http-fastcgi-temp-path=/var/lib/nginx/fastcgi \
--http-proxy-temp-path=/var/lib/nginx/proxy \
--http-scgi-temp-path=/var/lib/nginx/scgi \
--http-uwsgi-temp-path=/var/lib/nginx/uwsgi \
--with-debug \
--with-pcre-jit \
--with-ipv6 \
--with-http_ssl_module \
--with-http_stub_status_module \
--with-http_realip_module \
--with-http_auth_request_module \
--with-http_addition_module \
--with-http_dav_module \
--with-http_geoip_module \
--with-http_gunzip_module \
--with-http_gzip_static_module \
--with-http_image_filter_module \
--with-http_v2_module \
--with-http_sub_module \
--with-http_xslt_module \
--with-stream \
--with-stream_ssl_module \
--with-mail \
--with-mail_ssl_module \
--with-threads \
--add-module=/home/vagrant/truckertracker/nginx/ngx_http_auth_pam_module-1.5.1 \
--add-module=/home/vagrant/truckertracker/nginx/nginx-dav-ext-module-0.0.3 \
--add-module=/home/vagrant/truckertracker/nginx/echo-nginx-module-0.60 \
--add-module=/home/vagrant/truckertracker/nginx/gnosek-nginx-upstream-fair-a18b409 \
--add-module=/home/vagrant/truckertracker/nginx/ngx_http_substitutions_filter_module-0.6.4 \
--add-module=/home/vagrant/truckertracker/nginx/nchan-1.0.3

echo "*** make"

make
sudo make install

echo "configuring nginx nchan"

sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.old
sudo sed -i -e 's/worker_processes auto;/worker_processes 5;/g' /etc/nginx/nginx.conf

sudo cp /vagrant/nginx/local.truckertracker.com.au /etc/nginx/sites-available/

sudo mkdir -p /var/cache/nginx/client_temp

sudo systemctl daemon-reload
sudo systemctl restart nginx.service

echo "*>*>*> NGINX NCHAN Installed configured <*<*<*"
echo ""