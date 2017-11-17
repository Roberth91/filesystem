# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "bento/centos-6.7"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 8000, host: 8080
  config.vm.network "forwarded_port", guest: 3316, host: 13316

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"
  config.vm.synced_folder "../ft-filesystem", "/home/vagrant/filesystem"
  #, type: "nfs"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Use VBoxManage to customize the VM. For example to change memory:
  #   vb.memory = 4096
  #   vb.cpus = 2
  # end

  config.vm.provider "virtualbox" do |v|
    host = RbConfig::CONFIG['host_os']

    # Give VM 1/4 system memory & access to all cpu cores on the host
    if host =~ /darwin/
      cpus = `sysctl -n hw.ncpu`.to_i
      # sysctl returns Bytes and we need to convert to MB
      mem = `sysctl -n hw.memsize`.to_i / 1024 / 1024 / 4
    elsif host =~ /linux/
      cpus = `nproc`.to_i
      # meminfo shows KB and we need to convert to MB
      mem = `grep 'MemTotal' /proc/meminfo | sed -e 's/MemTotal://' -e 's/ kB//'`.to_i / 1024 / 4
    else # sorry Windows folks, I can't help you
      cpus = 2
      mem = 1024
    end

    v.customize ["modifyvm", :id, "--memory", mem]
    v.customize ["modifyvm", :id, "--cpus", cpus]
  end

  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
  # Install default tools
    sudo setenforce 0
    sudo yum -y update
    sudo yum groupinstall -y "Development Tools"
    sudo yum install -y nano wget sed tmpwatch tar ntp
    sudo chkconfig ntpd on
    sudo service ntpd start

    #EPEL for php mycrypt to install correctly
    sudo rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
    sudo rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm

    # Install specific tools for filesystem
    # => # PHP
    sudo rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
    sudo yum install -y php70w php70w-opcache php70w-cli php70w-fpm php70w-phpdbg php70w-common php70w-devel php70w-mbstring php70w-mcrypt php70w-pdo php70w-mysql php70w-ftp php70w-posix php70w-xml php70w-pear php70w-pecl-xdebug
    sudo sed -i '/^;date.timezone/c\date.timezone = UTC' /etc/php.ini

    # => # Composer
    sudo curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

    # => # Increase composer timeout
    sudo echo "export COMPOSER_PROCESS_TIMEOUT=1200" >> "/home/vagrant/.bashrc"

    # => # LFTP
    sudo yum install -y lftp

    # => # OpenSSL
    sudo yum install -y openssl-devel

    # => # XDebug
    sudo wget http://xdebug.org/files/xdebug-2.4.1.tgz
    tar xvzf xdebug-2.4.1.tgz
    cd xdebug-2.4.1
    phpize
    sudo ./configure
    sudo make
    sudo cp modules/xdebug.so /usr/lib64/php/modules
    sudo sh -c 'echo "zend_extension = /usr/lib64/php/modules/xdebug.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`'

    # => # ProFTP
    sudo rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
    sudo yum install -y proftpd
    sudo yum install -y ftp
    sudo proftpd
    sudo chkconfig proftpd on

    mkdir /home/vagrant/vagrantuploads && chown -R vagrant:vagrant /home/vagrant/vagrantuploads

    # Add an ssh key for the SFTP test
    grep -e "generated-key" ~/.ssh/authorized_keys || echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAAgQCdiXOCRt6XyAH5eHncgh5AHoSiLP8AcQlu0djUYoFkUKoowoLjz2CWvL1FNV1juHlrNGxLNqHiYC36Oy+PlCQ6ex6dFNHI+VAK7uv/2W/Enaf7Dplz9R3j1w1yFJwYfJUELeJAA0GeKVHb6D/BruyKuVBUtxgwJeXzrh+7lQB49w== generated-key" >> ~/.ssh/authorized_keys
    grep -e "vagrant@localhost.localdomain" ~/.ssh/authorized_keys || echo "ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAtdqaF/EeIBm2c0t3jgPuFAn5gOTukEXl6SiMTSUai4sExRb0w/K2RJkf3ptZZNrx4De9raRFlyqwexVtGRb7fMHNDcAwKoX8c/z0PCvd1B1Sfgcs2IRFP1PnTEjmO7k8Z4TZDmxhQMWZj8RVzxvlLgXHjUEVoiPOhgF4YFcU0RgfPW7CtZKhfsDEZco3J7oUYBsNDMusOvLB4g06KOwPlYPutQQExYfzSxbRoYKF7Xk431ucMbDJr205GNwm2pgN1Tof24xas/gNY0dseFcgbo47UwpvU80ItjPL6BJFjbw2JwIr9tj3cqZrWdsasYh7QHskwLQkBC3eXAbYMex/dQ== vagrant@localhost.localdomain" >> ~/.ssh/authorized_keys

    # Land in the filesystem directory by default
    echo "cd filesystem" >> /home/vagrant/.bashrc
  SHELL
end