# Circuit Breaker, an implementation for resilient PHP applications

[![codecov](https://codecov.io/gh/PrestaShop/circuit-breaker/branch/master/graph/badge.svg)](https://codecov.io/gh/PrestaShop/circuit-breaker) [![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/) [![Psalm](https://img.shields.io/badge/Psalm-Level%20Max-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/) [![Build Status](https://travis-ci.com/PrestaShop/circuit-breaker.svg?branch=master)](https://travis-ci.com/PrestaShop/circuit-breaker) 

## Main principles

![circuit breaker](https://user-images.githubusercontent.com/1247388/49721725-438bd700-fc63-11e8-8498-82ca681b15fb.png)

This library is compatible with PHP 5.6+.

## Installation

```
composer require prestashop/circuit-breaker
```

## Use

### Simple Circuit Breaker

You can use the factory to create a simple circuit breaker.

By default, you need to define 3 parameters for the circuit breaker:

* the **failures**: define how many times we try to access the service;
* the **timeout**: define how much time we wait before consider the service unreachable;
* the **threshold**: define how much time we wait before trying to access again the service (once it is considered unreachable);

The **fallback** callback will be used if the distant service is unreachable when the Circuit Breaker is Open (means "is used" if the service is unreachable). 

> You'd better return the same type of response expected from your distant call.

```php
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\FactorySettings;

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$circuitBreaker = $circuitBreakerFactory->create(new FactorySettings(2, 0.1, 10));

$fallbackResponse = function () {
    return '{}';
};

$response = $circuitBreaker->call('https://api.domain.com', [], $fallbackResponse);
```

If you don't specify any fallback, by default the circuit breaker will return an empty string.

```php
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\FactorySettings;

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$circuitBreaker = $circuitBreakerFactory->create(new FactorySettings(2, 0.1, 10));
$response = $circuitBreaker->call('https://unreacheable.api.domain.com', []); // $response == ''
```

You can also define the client options (or even set your own client if you prefer).

```php
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\FactorySettings;

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$settings = new FactorySettings(2, 0.1, 10);
$settings->setClientOptions(['method' => 'POST']);
$circuitBreaker = $circuitBreakerFactory->create($settings);
$response = $circuitBreaker->call('https://api.domain.com/create/user', ['body' => ['firstname' => 'John', 'lastname' => 'Doe']]);
```

> For the Guzzle implementation, the Client options are described
> in the [HttpGuzzle documentation](http://docs.guzzlephp.org/en/stable/index.html).

### Advanced Circuit Breaker

If you need more control on your circuit breaker, you should use the `AdvancedCircuitBreaker` which manages more features:

* the **stripped failures**: define how many times we try to access the service when the circuit breaker is Half Open (when it retires to reach the service after it was unreachable);
* the **stripped timeout**: define how much time we wait before consider the service unreachable (again in Half open state);
* the **storage**: used to store the circuit breaker states and transitions. By default it's an `SimpleArray` so if you want to "cache" the fact that your service is unreachable you should use a persistent storage;
* the **transition dispatcher**: used if you need to subscribe to transition events (ex: a dispatcher based on Symfony EventDispatcher is available)

#### Storage

```php
use Doctrine\Common\Cache\FilesystemCache;
use PrestaShop\CircuitBreaker\AdvancedCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\Storage\DoctrineCache;

$circuitBreakerFactory = new AdvancedCircuitBreakerFactory();
$settings = new FactorySettings(2, 0.1, 60); //60 seconds threshold

//Once the circuit breaker is open, the fallback response will be returned instantly during the next 60 seconds
//Since the state is persisted even other requests/processes will be aware that the circuit breaker is open
$doctrineCache = new FilesystemCache(_PS_CACHE_DIR_ . '/addons_category');
$storage = new DoctrineCache($doctrineCache);
$settings->setStorage($storage);

$circuitBreaker = $circuitBreakerFactory->create($settings);
$response = $circuitBreaker->call('https://unreachable.api.domain.com/create/user', []);
```

### Guzzle Cache

Besides caching the circuit breaker state, it may be interesting to cache the successful responses. The circuit breaker library doesn't manage the cache itself,
however you can use [Guzzle Subscriber](https://github.com/guzzle/cache-subscriber) to manage it.

```php
use Doctrine\Common\Cache\FilesystemCache;
use GuzzleHttp\Subscriber\Cache\CacheStorage;
use GuzzleHttp\Subscriber\Cache\CacheSubscriber;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$settings = new FactorySettings(2, 0.1, 60); //60 seconds threshold

//Guzzle subsriber is also compatible with doctrine cache, which is why we proposed a storage based on it, this allows you to use for storage and cache
$doctrineCache = new FilesystemCache(_PS_CACHE_DIR_ . '/addons_category');
//By default the ttl is defined by the response headers but you can set a default one
$cacheStorage = new CacheStorage($doctrineCache, null, 60);
$cacheSubscriber = new CacheSubscriber($cacheStorage, function (Request $request) { return true; });

$settings->setClientOptions(['subscribers' => [$cacheSubscriber]]);

$circuitBreaker = $circuitBreakerFactory->create($settings);
$response = $circuitBreaker->call('https://api.domain.com/create/user', []);
```

## Tests

```
composer test
```

## Code quality

```
composer cs-fix && composer phpcb && composer psalm && composer phpcs
```

We also use [PHPQA](https://github.com/EdgedesignCZ/phpqa#phpqa) to check the Code quality
during the CI management of the contributions.

If you want to use it (using Docker):

```
docker run --rm -u $UID -v $(pwd):/app eko3alpha/docker-phpqa --report --ignoredDirs vendor,tests
```

If you want to use it (using Composer):

```
composer global require edgedesign/phpqa=v1.20.0 --update-no-dev
phpqa --report --ignoredDirs vendor,tests
```
