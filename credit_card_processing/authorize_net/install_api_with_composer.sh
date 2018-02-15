# In a chosen created directory within the web based folder example: /var/www/html/composer/
sudo mkdir /var/www/html/composer/

# Make that directory writeable fully
sudo chmod -R 777 composer/

# Inside of that directory create the file composer.json
sudo vi composer.json

# Enter the following and save
{
  "require": {
  "php": ">=5.6",
  "authorizenet/authorizenet": "~1.9"
  }
}

# Install or update the composer in the same directory composer.json has been created! full rights 777
composer update
# OR
composer install

# now for the function processOnlinePaymentAuthorizeNet these following commands will be ok
require ($_SERVER["DOCUMENT_ROOT"]."/plugin/authorize/vendor/autoload.php") ;
use net\authorize\api\contract\v1 as AnetAPI ;
use net\authorize\api\controller as AnetController ;
