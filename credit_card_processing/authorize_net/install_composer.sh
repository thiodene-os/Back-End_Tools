# We recommend using Composer. (Note: we never recommend you override the new secure-http default setting). 
# Install/Update your composer.json file as per the example below and then run composer update.

# After CURL has been installed... see install_curl.sh:
sudo apt-get update

# Download the installer:
sudo curl -s https://getcomposer.org/installer | php

Move the composer.phar file:
sudo mv composer.phar /usr/local/bin/composer

# Verify composer is installed, following command outputs the Version of the composer
composer
