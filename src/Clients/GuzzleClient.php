<?php

namespace PrestaShop\CircuitBreaker\Clients;

use Exception;
use GuzzleHttp\Client as OriginalGuzzleClient;
use GuzzleHttp\Subscriber\Mock;
use PrestaShop\CircuitBreaker\Contracts\ClientInterface;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableServiceException;
use PrestaShop\CircuitBreaker\Exceptions\UnsupportedMethodException;

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
     * @var array the Client main options
     */
    private $mainOptions;

    public function __construct(array $mainOptions = [])
    {
        $this->mainOptions = $mainOptions;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnavailableServiceException
     */
    public function request($resource, array $options)
    {
        try {
            $client = $this->buildClient();
            $method = $this->getHttpMethod($options);
            $options['exceptions'] = true;

            // prevents unhandled method errors in Guzzle 5
            unset($options['method']);

            $request = $client->createRequest($method, $resource, $options);

            return (string) $client->send($request)->getBody();
        } catch (Exception $e) {
            throw new UnavailableServiceException($e->getMessage(), $e->getCode(), $e);
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
        if (isset($this->mainOptions['method'])) {
            return $this->mainOptions['method'];
        }

        if (isset($options['method'])) {
            if (!array_key_exists($options['method'], self::SUPPORTED_METHODS)) {
                throw UnsupportedMethodException::unsupportedMethod($options['method']);
            }

            return $options['method'];
        }

        return self::DEFAULT_METHOD;
    }

    /**
     * @return OriginalGuzzleClient
     */
    private function buildClient()
    {
        if (isset($this->mainOptions['mock']) && $this->mainOptions['mock'] instanceof Mock) {
            return $this->buildMockClient($this->mainOptions['mock']);
        }

        return new OriginalGuzzleClient($this->mainOptions);
    }

    /**
     * Builds a client with a mock
     *
     * @return OriginalGuzzleClient
     */
    private function buildMockClient(Mock $mock)
    {
        $options = $this->mainOptions;
        unset($options['mock']);

        $client = new OriginalGuzzleClient($options);

        $client->getEmitter()->attach($mock);

        return $client;
    }
}
