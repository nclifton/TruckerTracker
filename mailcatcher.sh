#!/usr/bin/env bash

echo "** INSTALLING MAILCATCHER **"

# Update repositories
sudo apt-get update

# Install Basics
# build-essential needed for "make" command
sudo apt-get install -y build-essential software-properties-common vim curl wget tmux

# Install Mailcatcher Dependencies (sqlite, ruby)
sudo apt-get install -y libsqlite3-dev ruby2.3-dev

# Install Mailcatcher as a Ruby gem
sudo gem install mailcatcher


cat >/tmp/mailcatcher.service << EOL
[Unit]
Description=Mailcatcher
After=network-online.target

[Service]
ExecStart=/usr/local/bin/mailcatcher --foreground --http-ip=0.0.0.0

[Install]
WantedBy=multi-user.target
EOL

sudo mv /tmp/mailcatcher.service /etc/systemd/system/

sudo systemctl restart mailcatcher