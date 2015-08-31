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
$params = array(
    CentralNews\Service\Client::URL => 'http://localhost',
    CentralNews\Service\Client::TOKEN => 'xxxxxxxxxxxxxxx',
    CentralNews\Service\Client::USER => 'user',
    CentralNews\Service\Client::PASSWORD => 'password',
);

$client = new CentralNews\Service\Client($params);

// proměnné použité dále v příkladech
$groupId = 1; // je číselný identifikátor skupiny (1,2, ..)
$email = "info@itstudio.cz"; // je platná emailová adresa
```

## Vytvoření nové odběratelské skupiny
```php
$newGroup = new CentralNews\Entity\SubscriberGroup();
$newGroup->setName('New Group');
$newGroup->setDescription('description');

$subscriberManager = $client->getSubscriberManager();
$subscriberManager->addGroup($newGroup);
```

## Výpis odběratelských skupin
```php
$subscriberManager = $client->getSubscriberManager();
$groups = $subscriberManager->getGroups();
```

## Počet všech odběratelů
```php
$subscriberManager = $client->getSubscriberManager();
$count = $subscriberManager->getSubscribersCount();
```

## Počet odběratelů dané skupiny
```php
$subscriberManager = $client->getSubscriberManager();
$group = new \CentralNews\Entity\Group($groupId);
$count = $subscriberManager->getSubscribersCount($group);
```

## Informace o odběrateli
```php
$group = new \CentralNews\Entity\Group($groupId);
$subscriberManager = $client->getSubscriberManager();
$subscriber = $subscriberManager->getSubscriber($email, $group);
```

## Vymazání odběratele ze skupiny
```php
$group = new \CentralNews\Entity\Group($groupId);
$subscriberManager = $client->getSubscriberManager();
$subscriberManager->deleteSubscriber($email, $group);
```

## Přidání odběratelů
- když existují, aktualizují se jejich údaje
```php
$subscribers[] = new CentralNews\Entity\Subscriber($email);
$group = new CentralNews\Entity\Group($groupId);
$subscriberManager = $client->getSubscriberManager();
$subscriberManager->saveSubscribers($subscribers, $group);
```

- když odběratel existuje, přeskočí se (neaktualizuje se)
```php
$subscribers[] = new CentralNews\Entity\Subscriber($email);
$group = new CentralNews\Entity\Group($groupId);
$subscriberManager = $client->getSubscriberManager();
$subscriberManager->importSubscribers($subscribers, $group);
```

## Událost - opuštěný košík
```php
$eventManager = $client->getEventManager();
$lostCart = new \CentralNews\Entity\LostCart();
$lostCart->setEmail($email);
$product = new CentralNews\Entity\Product();
$product->setName('product name');
$product->setPrice(100);
$lostCart->addProduct($product);
$eventManager->callLostCart($lostCart);
```

## Událost - opuštěný košík
```php
$eventManager = $client->getEventManager();
$lostCart = new \CentralNews\Entity\LostCart();
$lostCart->setEmail($email);
$product = new CentralNews\Entity\Product();
$product->setName('product name');
$product->setPrice(100);
$lostCart->addProduct($product);
$eventManager->callLostCart($lostCart);
```

## Odeslání vlastního požadavku
```php
$manager = $client->getManager();
$request = new CentralNews\Service\Request($operation, $params);
$response = $manager->sendRequest($request);
```
