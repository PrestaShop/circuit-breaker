<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace Tests\PrestaShop\CircuitBreaker;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\AdvancedCircuitBreaker;
use PrestaShop\CircuitBreaker\AdvancedCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\TransitionDispatcher;
use PrestaShop\CircuitBreaker\Transitions;

class AdvancedCircuitBreakerFactoryTest extends TestCase
{
    /**
     * @dataProvider getSettings
     *
     * @param array $settings the Circuit Breaker settings
     *
     * @return void
     */
    public function testCircuitBreakerCreation(array $settings)
    {
        $factory = new AdvancedCircuitBreakerFactory();
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
    }

    public function testCircuitBreakerWithDispatcher()
    {
        $dispatcher = $this->getMockBuilder(TransitionDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $localeService = 'file://' . __FILE__;
        $expectedParameters = ['toto' => 'titi', 42 => 51];

        $dispatcher
            ->expects($this->at(0))
            ->method('dispatchTransition')
            ->with(
                $this->equalTo(Transitions::INITIATING_TRANSITION),
                $this->equalTo($localeService),
                $this->equalTo([])
            )
        ;
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatchTransition')
            ->with(
                $this->equalTo(Transitions::TRIAL_TRANSITION),
                $this->equalTo($localeService),
                $this->equalTo($expectedParameters)
            )
        ;

        $factory = new AdvancedCircuitBreakerFactory();
        $circuitBreaker = $factory->create([
            'closed' => [
                'failures' => 2,
                'timeout' => 0.1,
                'threshold' => 0,
            ],
            'open' => [
                'failures' => 0,
                'timeout' => 0,
                'threshold' => 10,
            ],
            'half_open' => [
                'failures' => 1,
                'timeout' => 0.2,
                'threshold' => 0,
            ],
            'dispatcher' => $dispatcher,
        ]);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
        $circuitBreaker->call($localeService, function () {}, $expectedParameters);
    }

    public function testCircuitBreakerWithStorage()
    {
        $storage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $factory = new AdvancedCircuitBreakerFactory();
        $circuitBreaker = $factory->create([
            'closed' => [
                'failures' => 2,
                'timeout' => 0.1,
                'threshold' => 0,
            ],
            'open' => [
                'failures' => 0,
                'timeout' => 0,
                'threshold' => 10,
            ],
            'half_open' => [
                'failures' => 1,
                'timeout' => 0.2,
                'threshold' => 0,
            ],
            'storage' => $storage,
        ]);

        $this->assertInstanceOf(AdvancedCircuitBreaker::class, $circuitBreaker);
    }

    public function testFactoryDefaultSettings()
    {
        $defaultSettings = [
            'closed' => [
                'failures' => 2,
                'timeout' => 0.1,
                'threshold' => 0,
            ],
        ];
        $factory = new AdvancedCircuitBreakerFactory($defaultSettings);
        $circuitBreaker = $factory->create([
            'open' => [
                'failures' => 0,
                'timeout' => 0,
                'threshold' => 10,
            ],
            'half_open' => [
                'failures' => 1,
                'timeout' => 0.2,
                'threshold' => 0,
            ],
        ]);
        $this->assertNotNull($circuitBreaker);
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            [
                [
                    'closed' => [
                        'failures' => 2,
                        'timeout' => 0.1,
                        'threshold' => 0,
                    ],
                    'open' => [
                        'failures' => 0,
                        'timeout' => 0,
                        'threshold' => 10,
                    ],
                    'half_open' => [
                        'failures' => 1,
                        'timeout' => 0.2,
                        'threshold' => 0,
                    ],
                    'client' => ['proxy' => '192.168.16.1:10'],
                ],
            ],
            [
                [
                    'closed' => [
                        'failures' => 2,
                        'timeout' => 0.1,
                        'threshold' => 0,
                    ],
                    'open' => [
                        'failures' => 0,
                        'timeout' => 0,
                        'threshold' => 10,
                    ],
                    'half_open' => [
                        'failures' => 1,
                        'timeout' => 0.2,
                        'threshold' => 0,
                    ],
                    'client' => new GuzzleClient(['proxy' => '192.168.16.1:10']),
                ],
            ],
        ];
    }
}
