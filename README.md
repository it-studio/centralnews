# CentralNews API
API knihovna pro práci se systémem CentralNews.

# Instalace
Composer: ```composer require itstudiocz/centralnews-api-php```


# Příklady použití
## Vytvoření instance klienta
```php
$client = new CentralNews\Service\Client;

$client->setServiceUrl('http://centralnews.itstudio.cz/ws/cnews_import');
$client->setApiKey('Váš API klíč');
$client->setUser('Váš identifikátor');
$client->setPassword('Vaše heslo');
```