# Source:
# https://getcomposer.org/download/

# Download Composer Latest: v1.8.4
# Windows Installer

# The installer will download composer for you and set up your PATH environment variable so you can simply call composer from any directory.

# Download and run Composer-Setup.exe - it will install the latest composer version whenever it is executed.
# Command-line installation

# To quickly install Composer in the current directory, run the following script in your terminal. To automate the installation, use the guide on installing Composer programmatically.

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"


# Now use composer (just a few example of how to use it...):

# Test composer.phar
php composer.phar
# Now install library creating /vendor folder at the same time
php composer.phar require phpmailer/phpmailer
# Other commands
php composer.phar install
php composer.phar update --no-scripts
