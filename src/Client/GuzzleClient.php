<?php

namespace PrestaShop\CircuitBreaker\Client;

use Exception;
use GuzzleHttp\Client as OriginalGuzzleClient;
use GuzzleHttp\Subscriber\Mock;
use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Exception\UnavailableServiceException;
use PrestaShop\CircuitBreaker\Exception\UnsupportedMethodException;

/**
 * Guzzle implementation of client.
 * The possibility of extending this client is intended.
 */
class GuzzleClient implements ClientInterface
{
    /**
     * @var string by default, calls are sent using GET method
     */
    const DEFAULT_METHOD = 'GET';

    /**
     * Supported HTTP methods
     */
    const SUPPORTED_METHODS = [
        'GET' => true,
        'HEAD' => true,
        'POST' => true,
        'PUT' => true,
        'DELETE' => true,
        'OPTIONS' => true,
    ];

    /**
     * @var array the Client default options
     */
    private $defaultOptions;

    public function __construct(array $defaultOptions = [])
    {
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnavailableServiceException
     */
    public function request($resource, array $options)
    {
        try {
            $options = array_merge($this->defaultOptions, $options);
            $client = $this->buildClient($options);
            $method = $this->getHttpMethod($options);
            $options['exceptions'] = true;

            // prevents unhandled method errors in Guzzle 5
            unset($options['method'], $options['mock']);

            $request = $client->createRequest($method, $resource, $options);

            return (string) $client->send($request)->getBody();
        } catch (Exception $e) {
            throw new UnavailableServiceException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param array $options the list of options
     *
     * @return string the method
     *
     * @throws UnsupportedMethodException
     */
    private function getHttpMethod(array $options)
    {
        if (isset($options['method'])) {
            if (!array_key_exists($options['method'], self::SUPPORTED_METHODS)) {
                throw UnsupportedMethodException::unsupportedMethod($options['method']);
            }

            return $options['method'];
        }

        return self::DEFAULT_METHOD;
    }

    /**
     * @param array $options
     *
     * @return OriginalGuzzleClient
     */
    private function buildClient(array $options)
    {
        if (isset($options['mock']) && $options['mock'] instanceof Mock) {
            return $this->buildMockClient($options);
        }

        return new OriginalGuzzleClient($options);
    }

    /**
     * Builds a client with a mock
     *
     * @param array $options
     *
     * @return OriginalGuzzleClient
     */
    private function buildMockClient(array $options)
    {
        /** @var Mock $mock */
        $mock = $options['mock'];
        unset($options['mock']);

        $client = new OriginalGuzzleClient($options);

        $client->getEmitter()->attach($mock);

        return $client;
    }
}
