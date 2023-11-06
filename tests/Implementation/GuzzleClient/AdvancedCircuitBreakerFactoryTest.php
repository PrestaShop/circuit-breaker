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

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\AdvancedCircuitBreaker;
use PrestaShop\CircuitBreaker\AdvancedCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\Client\GuzzleClient;
use PrestaShop\CircuitBreaker\Contract\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransitionDispatcherInterface;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\State;
use PrestaShop\CircuitBreaker\Transition;

class AdvancedCircuitBreakerFactoryTest extends TestCase
{
    /**
     * @dataProvider getSettings
     *
     * @param FactorySettingsInterface $settings the Circuit Breaker settings
     */
    public function testCircuitBreakerCreation(FactorySettingsInterface $settings): void
    {
        $factory = new AdvancedCircuitBreakerFactory();
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
    }

    public function testCircuitBreakerWithDispatcher(): void
    {
        $dispatcher = $this->getMockBuilder(TransitionDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $localeService = 'file://' . __FILE__;
        $expectedParameters = ['toto' => 'titi', 42 => 51];

        $dispatcher
            ->expects($this->exactly(3))
            ->method('dispatchTransition')
            ->withConsecutive(
                [
                    $this->equalTo(Transition::INITIATING_TRANSITION),
                    $this->equalTo($localeService),
                    $this->equalTo([]),
                ],
                [
                    $this->equalTo(Transition::TRIAL_TRANSITION),
                    $this->equalTo($localeService),
                    $this->equalTo($expectedParameters),
                ]
            )
        ;

        $factory = new AdvancedCircuitBreakerFactory();
        $settings = new FactorySettings(2, 0.1, 10);
        $settings
            ->setStrippedTimeout(0.2)
            ->setDispatcher($dispatcher)
        ;

        $mock = new MockHandler([
            new Response(200, [], Utils::streamFor('{"hello": "world"}')),
        ]);
        $client = new GuzzleClient(['handler' => $mock]);
        $settings->setClient($client);

        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
        $circuitBreaker->call($localeService, $expectedParameters, function () {
            return false;
        });
    }

    public function testCircuitBreakerWithStorage(): void
    {
        $storage = $this->getMockBuilder(StorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $factory = new AdvancedCircuitBreakerFactory();
        $settings = new FactorySettings(2, 0.1, 10);
        $settings
            ->setStrippedTimeout(0.2)
            ->setStorage($storage)
        ;
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
    }

    public function testCircuitBreakerWithDefaultFallback(): void
    {
        $factory = new AdvancedCircuitBreakerFactory();
        $settings = new FactorySettings(2, 0.1, 10);
        $settings->setDefaultFallback(function () {
            return 'default_fallback';
        });
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
        $response = $circuitBreaker->call('unknown_service');
        $this->assertEquals(State::OPEN_STATE, $circuitBreaker->getState());
        $this->assertEquals('default_fallback', $response);
    }

    public function getSettings(): array
    {
        return [
            [
                (new FactorySettings(2, 0.1, 10))
                    ->setStrippedTimeout(0.2)
                    ->setClientOptions(['proxy' => '192.168.16.1:10']),
            ],
            [
                (new FactorySettings(2, 0.1, 10))
                    ->setStrippedTimeout(0.2)
                    ->setClient(new GuzzleClient(['proxy' => '192.168.16.1:10'])),
            ],
        ];
    }
}
