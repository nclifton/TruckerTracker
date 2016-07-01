require 'json'
require 'yaml'

VAGRANTFILE_API_VERSION ||= "2"
confDir = $confDir ||= File.expand_path("vendor/laravel/homestead", File.dirname(__FILE__))

homesteadYamlPath = "Homestead.yaml"
homesteadJsonPath = "Homestead.json"
afterScriptPath = "after.sh"
aliasesPath = "aliases"

require File.expand_path(confDir + '/scripts/homestead.rb')

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    if File.exists? aliasesPath then
        config.vm.provision "file", source: aliasesPath, destination: "~/.bash_aliases"
    end

    if File.exists? homesteadYamlPath then
        Homestead.configure(config, YAML::load(File.read(homesteadYamlPath)))
    elsif File.exists? homesteadJsonPath then
        Homestead.configure(config, JSON.parse(File.read(homesteadJsonPath)))
    end

    if File.exists? afterScriptPath then
        config.vm.provision "shell", path: afterScriptPath
    end
    config.vm.provision "shell", inline: <<-SHELL
        /bin/bash /vagrant/mongodb.sh
        /bin/bash /vagrant/install_nginx_nchan.sh
        echo "xdebug.remote_enable = 1" >> /etc/php/7.0/mods-available/xdebug.ini
        echo "xdebug.remote_connect_back = 1" >> /etc/php/7.0/mods-available/xdebug.ini
        echo "xdebug.remote_port = 9000" >> /etc/php/7.0/mods-available/xdebug.ini
        echo "xdebug.scream=0" >> /etc/php/7.0/mods-available/xdebug.ini
        echo "xdebug.cli_color=1" >> /etc/php/7.0/mods-available/xdebug.ini
        echo "xdebug.show_local_vars=1" >> /etc/php/7.0/mods-available/xdebug.ini
        echo "error_log = /var/log/php7.0-fpm-error.log" >> /etc/php/7.0/fpm/php.ini
    SHELL
end
