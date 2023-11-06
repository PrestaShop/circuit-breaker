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
declare(strict_types=1);

namespace Tests\PrestaShop\CircuitBreaker;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Client\GuzzleClient;
use ReflectionClass;
use ReflectionException;

/**
 * Helper to get a fake Guzzle client.
 */
abstract class CircuitBreakerTestCase extends TestCase
{
    /**
     * Returns an instance of Client able to emulate
     * available and not available services.
     */
    protected function getTestClient(): GuzzleClient
    {
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new Response(200, [], Utils::streamFor('{"hello": "world"}')),
        ]);

        return new GuzzleClient(['handler' => $mock]);
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/3888
     *
     * @throws ReflectionException
     */
    protected static function invocations(AnyInvokedCount $anyInvokedCount): array
    {
        $reflectionClass = new ReflectionClass(get_class($anyInvokedCount));
        $parentReflectionClass = $reflectionClass->getParentClass();

        if ($parentReflectionClass instanceof ReflectionClass) {
            foreach ($parentReflectionClass->getProperties() as $property) {
                if ($property->getName() === 'invocations') {
                    $property->setAccessible(true);

                    return $property->getValue($anyInvokedCount);
                }
            }
        }

        return [];
    }
}
