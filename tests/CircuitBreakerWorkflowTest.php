<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\AdvancedCircuitBreaker;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Contracts\CircuitBreakerInterface;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableServiceException;
use PrestaShop\CircuitBreaker\States;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PrestaShop\CircuitBreaker\Transitions\NullDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use PrestaShop\CircuitBreaker\Storages\SymfonyCache;
use PrestaShop\CircuitBreaker\SymfonyCircuitBreaker;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Systems\MainSystem;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
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
        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
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
        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);

        //After two failed calls switch to OPEN state
        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());
        $this->assertSame(
            '{}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
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
        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());

        // OPEN - no call to client
        $response = $circuitBreaker->call('https://httpbin.org/get/foo', $this->createFallbackResponse());
        $this->assertSame('{}', $response);
        $this->assertSame(States::OPEN_STATE, $circuitBreaker->getState());

        sleep(2 * self::OPEN_THRESHOLD);
        // SWITCH TO HALF OPEN - retry to call the service
        $this->assertSame(
            '{"hello": "world"}',
            $circuitBreaker->call(
                'https://httpbin.org/get/foo',
                $this->createFallbackResponse()
            )
        );
        $this->assertSame(States::CLOSED_STATE, $circuitBreaker->getState());
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
        $this->assertEquals(States::CLOSED_STATE, $firstCircuitBreaker->getState());
        $firstCircuitBreaker->call('fake_service', function () { return false; });
        $this->assertEquals(States::OPEN_STATE, $firstCircuitBreaker->getState());
        $this->assertTrue($storage->hasTransaction('fake_service'));

        $secondCircuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $storage,
            new NullDispatcher()
        );
        $this->assertEquals(States::CLOSED_STATE, $secondCircuitBreaker->getState());
        $secondCircuitBreaker->call('fake_service', function () { return false; });
        $this->assertEquals(States::OPEN_STATE, $secondCircuitBreaker->getState());
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
