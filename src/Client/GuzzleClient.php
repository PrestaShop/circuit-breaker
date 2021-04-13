<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types = 1);

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
