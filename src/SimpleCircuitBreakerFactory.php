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

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Client\GuzzleClient;
use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;
use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\FactoryInterface;
use PrestaShop\CircuitBreaker\Contract\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\Place\ClosedPlace;
use PrestaShop\CircuitBreaker\Place\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Place\OpenPlace;

/**
 * Main implementation of Circuit Breaker Factory
 * Used to create a SimpleCircuitBreaker instance.
 */
final class SimpleCircuitBreakerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(FactorySettingsInterface $settings): CircuitBreakerInterface
    {
        $closedPlace = new ClosedPlace($settings->getFailures(), $settings->getTimeout(), 0);
        $openPlace = new OpenPlace(0, 0, $settings->getThreshold());
        $halfOpenPlace = new HalfOpenPlace($settings->getFailures(), $settings->getStrippedTimeout(), 0);

        /** @var ClientInterface $client */
        $client = $settings->getClient() ?: new GuzzleClient($settings->getClientOptions());

        return new SimpleCircuitBreaker(
            $openPlace,
            $halfOpenPlace,
            $closedPlace,
            $client
        );
    }
}
