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

use DateTime;
use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;
use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\SystemInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Transaction\SimpleTransaction;

abstract class PartialCircuitBreaker implements CircuitBreakerInterface
{
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
    abstract public function call(string $service, array $serviceParameters = [], callable $fallback = null): string;

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isOpened(): bool
    {
        return State::OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isHalfOpened(): bool
    {
        return State::HALF_OPEN_STATE === $this->currentPlace->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed(): bool
    {
        return State::CLOSED_STATE === $this->currentPlace->getState();
    }

    protected function callFallback(callable $fallback = null): string
    {
        if (null === $fallback) {
            return '';
        }

        return (string) call_user_func($fallback);
    }

    /**
     * @param string $state the Place state
     * @param string $service the service URI
     */
    protected function moveStateTo(string $state, string $service): bool
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
     */
    protected function initTransaction(string $service): TransactionInterface
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
     */
    protected function isAllowedToRetry(TransactionInterface $transaction): bool
    {
        return $transaction->getFailures() < $this->currentPlace->getFailures();
    }

    /**
     * @param TransactionInterface $transaction the Transaction
     */
    protected function canAccessService(TransactionInterface $transaction): bool
    {
        return $transaction->getThresholdDateTime() < new DateTime();
    }

    /**
     * Calls the client with the right information.
     *
     * @param string $service the service URI
     * @param array $parameters the service URI parameters
     */
    protected function request(string $service, array $parameters = []): string
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
