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

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;

class SimpleCircuitBreakerFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        $factory = new SimpleCircuitBreakerFactory();

        $this->assertInstanceOf(SimpleCircuitBreakerFactory::class, $factory);
    }

    /**
     * @depends testCreation
     * @dataProvider getSettings
     *
     * @param FactorySettingsInterface $settings the Circuit Breaker settings
     */
    public function testCircuitBreakerCreation(FactorySettingsInterface $settings): void
    {
        $factory = new SimpleCircuitBreakerFactory();
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(SimpleCircuitBreaker::class, $circuitBreaker);
    }

    public function getSettings(): array
    {
        return [
            [
                (new FactorySettings(2, 0.1, 10))
                    ->setStrippedTimeout(0.2)
                    ->setStrippedFailures(1),
            ],
            [
                (new FactorySettings(2, 0.1, 10))
                    ->setStrippedTimeout(0.2)
                    ->setStrippedFailures(1)
                    ->setClientOptions(['proxy' => '192.168.16.1:10']),
            ],
        ];
    }
}
