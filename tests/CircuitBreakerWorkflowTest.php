<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\AdvancedCircuitBreaker;
use PrestaShop\CircuitBreaker\Client\GuzzleClient;
use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;
use PrestaShop\CircuitBreaker\Exception\UnavailableServiceException;
use PrestaShop\CircuitBreaker\State;
use PrestaShop\CircuitBreaker\Storage\SimpleArray;
use PrestaShop\CircuitBreaker\Transition\NullDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use PrestaShop\CircuitBreaker\Storage\SymfonyCache;
use PrestaShop\CircuitBreaker\SymfonyCircuitBreaker;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\Place\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Place\ClosedPlace;
use PrestaShop\CircuitBreaker\System\MainSystem;
use PrestaShop\CircuitBreaker\Place\OpenPlace;
use Symfony\Component\Cache\Simple\ArrayCache;

class CircuitBreakerWorkflowTest extends CircuitBreakerTestCase
{
    const OPEN_THRESHOLD = 1;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        //For SimpleCircuitBreaker tests we need to clear the storage cache because it is stored in a static variable
        $storage = new SimpleArray();
        $storage->clear();
    }

    /**
     * When we use the circuit breaker on unreachable service
     * the fallback response is used.
     *
     * @dataProvider getCircuitBreakers
     *
     * @param CircuitBreakerInterface $circuitBreaker
     */
    public function testCircuitBreakerIsInClosedStateAtStart($circuitBreaker)
    {
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
    }

    /**
     * Once the number of failures is reached, the circuit breaker
     * is open. This time no calls to the services are done.
     *
     * @dataProvider getCircuitBreakers
     *
     * @param CircuitBreakerInterface $circuitBreaker
     */
    public function testCircuitBreakerWillBeOpenInCaseOfFailures($circuitBreaker)
    {
        // CLOSED
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', [], $this->createFallbackResponse());
        $this->assertSame('{}', $response);

        //After two failed calls switch to OPEN state
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                [],
                $this->createFallbackResponse()
            )
        );
    }

    /**
     * Once the number of failures is reached, the circuit breaker
     * is open. This time no calls to the services are done.
     *
     * @dataProvider getCircuitBreakers
     *
     * @param CircuitBreakerInterface $circuitBreaker
     */
    public function testCircuitBreakerWillBeOpenWithoutFallback($circuitBreaker)
    {
        // CLOSED
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo');
        $this->assertSame('', $response);

        //After two failed calls switch to OPEN state
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                [],
                $this->createFallbackResponse()
            )
        );
    }

    /**
     * In HalfOpen state, if the service is back we can
     * close the CircuitBreaker.
     *
     * @dataProvider getCircuitBreakers
     *
     * @param CircuitBreakerInterface $circuitBreaker
     */
    public function testOnceInHalfOpenModeServiceIsFinallyReachable($circuitBreaker)
    {
        // CLOSED - first call fails (twice)
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', [], $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());

        // OPEN - no call to client
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', [], $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertSame(State::OPEN_STATE, $circuitBreaker->getState());

        sleep(2 * self::OPEN_THRESHOLD);
        // SWITCH TO HALF OPEN - retry to call the service
        $this->assertSame(
            '{"hello": "world"}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                [],
                $this->createFallbackResponse()
            )
        );
        $this->assertSame(State::CLOSED_STATE, $circuitBreaker->getState());
        $this->assertTrue($circuitBreaker->isClosed());
    }

    /**
     * This is not useful for SimpleCircuitBreaker since it has a SimpleArray storage
     */
    public function testRememberLastTransactionState()
    {
        $system = new MainSystem(
            new ClosedPlace(1, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, 1)
        );
        $storage = new SymfonyCache(new ArrayCache());
        $client = $this->createMock(GuzzleClient::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new UnavailableServiceException())
        ;

        $firstCircuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $storage,
            new NullDispatcher()
        );
        $this->assertEquals(State::CLOSED_STATE, $firstCircuitBreaker->getState());
        $firstCircuitBreaker->call('fake_service', [], function () {
            return false;
        });
        $this->assertEquals(State::OPEN_STATE, $firstCircuitBreaker->getState());
        $this->assertTrue($storage->hasTransaction('fake_service'));

        $secondCircuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $storage,
            new NullDispatcher()
        );
        $this->assertEquals(State::CLOSED_STATE, $secondCircuitBreaker->getState());
        $secondCircuitBreaker->call('fake_service', [], function () {
            return false;
        });
        $this->assertEquals(State::OPEN_STATE, $secondCircuitBreaker->getState());
    }

    /**
     * Return the list of supported circuit breakers
     *
     * @return array
     */
    public function getCircuitBreakers()
    {
        return [
            'simple' => [$this->createSimpleCircuitBreaker()],
            'symfony' => [$this->createSymfonyCircuitBreaker()],
            'advanced' => [$this->createAdvancedCircuitBreaker()],
        ];
    }

    /**
     * @return SimpleCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSimpleCircuitBreaker()
    {
        return new SimpleCircuitBreaker(
            new OpenPlace(0, 0, self::OPEN_THRESHOLD), // threshold 1s
            new HalfOpenPlace(0, 0.2, 0), // timeout 0.2s to test the service
            new ClosedPlace(2, 0.2, 0), // 2 failures allowed, 0.2s timeout
            $this->getTestClient()
        );
    }

    /**
     * @return AdvancedCircuitBreaker the circuit breaker for testing purposes
     */
    private function createAdvancedCircuitBreaker()
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, self::OPEN_THRESHOLD)
        );

        $symfonyCache = new SymfonyCache(new ArrayCache());

        return new AdvancedCircuitBreaker(
            $system,
            $this->getTestClient(),
            $symfonyCache,
            new NullDispatcher()
        );
    }

    /**
     * @return SymfonyCircuitBreaker the circuit breaker for testing purposes
     */
    private function createSymfonyCircuitBreaker()
    {
        $system = new MainSystem(
            new ClosedPlace(2, 0.2, 0),
            new HalfOpenPlace(0, 0.2, 0),
            new OpenPlace(0, 0, self::OPEN_THRESHOLD)
        );

        $symfonyCache = new SymfonyCache(new ArrayCache());
        $eventDispatcherS = $this->createMock(EventDispatcher::class);

        return new SymfonyCircuitBreaker(
            $system,
            $this->getTestClient(),
            $symfonyCache,
            $eventDispatcherS
        );
    }

    /**
     * @return callable the fallback callable
     */
    private function createFallbackResponse()
    {
        return function () {
            return '{}';
        };
    }
}
