# CentralNews API
API knihovna pro pr�ci se syst�mem CentralNews.

# Instalace
Composer: ```composer require itstudiocz/centralnews-api-php```


# P��klady pou�it�
## Vytvo�en� instance klienta
```php
$client = new CentralNews\Service\Client;

$client->setServiceUrl('http://centralnews.itstudio.cz/ws/cnews_import');
$client->setApiKey('V� API kl��');
$client->setUser('V� identifik�tor');
$client->setPassword('Va�e heslo');
```