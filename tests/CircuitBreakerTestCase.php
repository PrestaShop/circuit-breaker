<?php

namespace Tests\PrestaShop\CircuitBreaker;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Client\GuzzleClient;

/**
 * Helper to get a fake Guzzle client.
 */
abstract class CircuitBreakerTestCase extends TestCase
{
    /**
     * Returns an instance of Client able to emulate
     * available and not available services.
     *
     * @return GuzzleClient
     */
    protected function getTestClient()
    {
        $mock = new Mock([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new Response(200, [], Stream::factory('{"hello": "world"}')),
        ]);

        return new GuzzleClient(['mock' => $mock]);
    }
}
