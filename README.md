# Circuit Breaker, an implementation for resilient PHP applications

[![codecov](https://codecov.io/gh/PrestaShop/circuit-breaker/branch/master/graph/badge.svg)](https://codecov.io/gh/PrestaShop/circuit-breaker)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/)
[![Psalm](https://img.shields.io/badge/Psalm-Level%20Max-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/)
[![Build](https://github.com/PrestaShop/circuit-breaker/actions/workflows/php.yml/badge.svg)](https://github.com/PrestaShop/circuit-breaker/actions/workflows/php.yml)

## Main principles

![circuit breaker](https://user-images.githubusercontent.com/1247388/49721725-438bd700-fc63-11e8-8498-82ca681b15fb.png)

This library is compatible with PHP 7.4+.

## Installation

```
composer require prestashop/circuit-breaker
```

## Use

### Symfony Http Client and Guzzle Client implementations

By default, Circuit Breaker use the Symfony Http Client library, and all the client options are described in the [official documentation](https://symfony.com/doc/current/http_client.html).

For retro-compatibility, we let you use Guzzle Client instead of Symfony Http Client. To use Guzzle, you need to set the Guzzle client with `setClient()` of the settings factory, like this example below:

```php
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\Client\GuzzleClient

$circuitBreakerFactory = new SimpleCircuitBreakerFactory();
$factorySettings = new FactorySettings(2, 0.1, 10);
$factorySettings->setClient(new GuzzleHttpClient());

$circuitBreaker = $circuitBreakerFactory->create($factorySettings);
```

Be aware, that the client options depend on the client implementation you choose!
 
> For the Guzzle implementation, the Client options are described
> in the [HttpGuzzle documentation](http://docs.guzzlephp.org/en/stable/index.html).

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
