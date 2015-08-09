# CentralNews API
API knihovna pro práci se systémem CentralNews.

## Dokumentace implementace
Připojení k CentralNews probíhá dle této dokumentace:
http://centralnews.itstudio.cz/bundles/centralnewssubscriber/doc/ws.pdf

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