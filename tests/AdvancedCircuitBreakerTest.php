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
use PrestaShop\CircuitBreaker\AdvancedCircuitBreaker;
use PrestaShop\CircuitBreaker\Client\GuzzleClient;
use PrestaShop\CircuitBreaker\Contract\TransitionDispatcherInterface;
use PrestaShop\CircuitBreaker\Place\ClosedPlace;
use PrestaShop\CircuitBreaker\Place\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Place\OpenPlace;
use PrestaShop\CircuitBreaker\State;
use PrestaShop\CircuitBreaker\Storage\SymfonyCache;
use PrestaShop\CircuitBreaker\System\MainSystem;
use PrestaShop\CircuitBreaker\Transition\NullDispatcher;
use Symfony\Component\Cache\Simple\ArrayCache;

class AdvancedCircuitBreakerTest extends CircuitBreakerTestCase
{
    /**
     * Used to track the dispatched events.
     *
     * @var AnyInvokedCount
     */
    private $spy;

    /**
     * We should see the circuit breaker initialized,
     * a call being done and then the circuit breaker closed.
     */
    public function testCircuitBreakerEventsOnFirstFailedCall(): void
    {
        $circuitBreaker = $this->createCircuitBreaker();

        $circuitBreaker->call(
            'https://httpbin.org/get/foo',
            ['toto' => 'titi'],
            function () {
                return '{}';
            }
        );

        /**
         * The circuit breaker is initiated
         * the 2 failed trials are done
         * then the conditions are met to open the circuit breaker
         */
        $invocations = self::invocations($this->spy);

        $this->assertCount(4, $invocations);
        $this->assertSame('INITIATING', $invocations[0]->getParameters()[0]);
        $this->assertSame('TRIAL', $invocations[1]->getParameters()[0]);
        $this->assertSame('TRIAL', $invocations[2]->getParameters()[0]);
        $this->assertSame('OPENING', $invocations[3]->getParameters()[0]);
    }

    public function testSimpleCall(): void
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $mock = new MockHandler([
            new Response(200, [], Utils::streamFor('{"hello": "world"}')),
        ]);
        $client = new GuzzleClient(['handler' => $mock]);

        $circuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $symfonyCache,
            new NullDispatcher()
        );

        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
        $this->assertEquals(0, $mock->count());
        $this->assertEquals('{"hello": "world"}', $response);
    }

    public function testOpenStateAfterTooManyFailures(): void
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
        ]);
        $client = new GuzzleClient(['handler' => $mock]);

        $circuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $symfonyCache,
            new NullDispatcher()
        );

        $response = $circuitBreaker->call('anything');

        $this->assertEquals(0, $mock->count());
        $this->assertEquals(false, $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());
    }

    public function testNoFallback()
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
        ]);
        $client = new GuzzleClient(['handler' => $mock]);

        $circuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $symfonyCache,
            new NullDispatcher()
        );

        $response = $circuitBreaker->call('anything');
        $this->assertEquals(0, $mock->count());
        $this->assertEquals('', $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());
    }

    public function testBackToClosedStateAfterSuccess(): void
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new Response(200, [], Utils::streamFor('{"hello": "world"}')),
        ]);
        $client = new GuzzleClient(['handler' => $mock]);

        $circuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $symfonyCache,
            new NullDispatcher()
        );

        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertEquals(1, $mock->count());
        $this->assertEquals(false, $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());

        //Stay in OPEN state
        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertEquals(1, $mock->count());
        $this->assertEquals(false, $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());

        sleep(2);
        //Switch to CLOSED state on success
        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertEquals(0, $mock->count());
        $this->assertEquals('{"hello": "world"}', $response);
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
    }

    public function testStayInOpenStateAfterFailure(): void
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );
        $symfonyCache = new SymfonyCache(new ArrayCache());
        $mock = new MockHandler([
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
            new RequestException('Service unavailable', new Request('GET', 'test')),
        ]);
        $client = new GuzzleClient(['handler' => $mock]);

        $circuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $symfonyCache,
            new NullDispatcher()
        );

        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertEquals(1, $mock->count());
        $this->assertEquals(false, $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());

        //Stay in OPEN state
        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertEquals(1, $mock->count());
        $this->assertEquals(false, $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());

        sleep(2);
        //Switch to OPEN state on failure
        $response = $circuitBreaker->call('anything', [], function () {
            return false;
        });
        $this->assertEquals(0, $mock->count());
        $this->assertEquals(false, $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());
    }

    /**
     * @return AdvancedCircuitBreaker the circuit breaker for testing purposes
     */
    private function createCircuitBreaker(): AdvancedCircuitBreaker
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );

        $symfonyCache = new SymfonyCache(new ArrayCache());
        /** @var TransitionDispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(TransitionDispatcherInterface::class);
        $dispatcher->expects($this->spy = $this->any())
            ->method('dispatchTransition')
        ;

        return new AdvancedCircuitBreaker(
            $system,
            $this->getTestClient(),
            $symfonyCache,
            $dispatcher
        );
    }
}
