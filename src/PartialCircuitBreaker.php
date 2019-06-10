<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Transaction\SimpleTransaction;
use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\SystemInterface;
use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use DateTime;

abstract class PartialCircuitBreaker implements CircuitBreakerInterface
{
    /**
     * @param SystemInterface $system
     * @param ClientInterface $client
     * @param StorageInterface $storage
     */
    public function __construct(
        SystemInterface $system,
        ClientInterface $client,
        StorageInterface $storage
    ) {
        $this->currentPlace = $system->getInitialPlace();
        $this->places = $system->getPlaces();
        $this->client = $client;
        $this->storage = $storage;
    }

    /**
     * @var ClientInterface the Client that consumes the service URI
     */
    protected $client;

    /**
     * @var PlaceInterface the current Place of the Circuit Breaker
     */
    protected $currentPlace;

    /**
     * @var PlaceInterface[] the Circuit Breaker places
     */
    protected $places = [];

    /**
     * @var StorageInterface the Circuit Breaker storage
     */
    protected $storage;

    /**
     * {@inheritdoc}
     */
    abstract public function call($service, array $serviceParameters = [], callable $fallback = null);

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isOpened()
    {
        return State::OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isHalfOpened()
    {
        return State::HALF_OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return State::CLOSED_STATE === $this->currentPlace->getState();
    }

    /**
     * @param callable|null $fallback
     *
     * @return string
     */
    protected function callFallback(callable $fallback = null)
    {
        if (null === $fallback) {
            return '';
        }

        return call_user_func($fallback);
    }

    /**
     * @param string $state the Place state
     * @param string $service the service URI
     *
     * @return bool
     */
    protected function moveStateTo($state, $service)
    {
        $this->currentPlace = $this->places[$state];
        $transaction = SimpleTransaction::createFromPlace(
            $this->currentPlace,
            $service
        );

        return $this->storage->saveTransaction($service, $transaction);
    }

    /**
     * @param string $service the service URI
     *
     * @return TransactionInterface
     */
    protected function initTransaction($service)
    {
        if ($this->storage->hasTransaction($service)) {
            $transaction = $this->storage->getTransaction($service);
            // CircuitBreaker needs to be in the same state as its last transaction
            if ($this->getState() !== $transaction->getState()) {
                $this->currentPlace = $this->places[$transaction->getState()];
            }
        } else {
            $transaction = SimpleTransaction::createFromPlace(
                $this->currentPlace,
                $service
            );

            $this->storage->saveTransaction($service, $transaction);
        }

        return $transaction;
    }

    /**
     * @param TransactionInterface $transaction the Transaction
     *
     * @return bool
     */
    protected function isAllowedToRetry(TransactionInterface $transaction)
    {
        return $transaction->getFailures() < $this->currentPlace->getFailures();
    }

    /**
     * @param TransactionInterface $transaction the Transaction
     *
     * @return bool
     */
    protected function canAccessService(TransactionInterface $transaction)
    {
        return $transaction->getThresholdDateTime() < new DateTime();
    }

    /**
     * Calls the client with the right information.
     *
     * @param string $service the service URI
     * @param array $parameters the service URI parameters
     *
     * @return string
     */
    protected function request($service, array $parameters = [])
    {
        return $this->client->request(
            $service,
            array_merge($parameters, [
                'connect_timeout' => $this->currentPlace->getTimeout(),
                'timeout' => $this->currentPlace->getTimeout(),
            ])
        );
    }
}
