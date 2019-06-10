<?php

namespace PrestaShop\CircuitBreaker\Contract;

use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;

/**
 * Store the transaction between the Circuit Breaker
 * and the tiers service.
 */
interface StorageInterface
{
    /**
     * Save the CircuitBreaker transaction.
     *
     * @param string $service The service name
     * @param TransactionInterface $transaction the transaction
     *
     * @return bool
     */
    public function saveTransaction($service, TransactionInterface $transaction);

    /**
     * Retrieve the CircuitBreaker transaction for a specific service.
     *
     * @param string $service the service name
     *
     * @return TransactionInterface
     *
     * @throws TransactionNotFoundException
     */
    public function getTransaction($service);

    /**
     * Checks if the transaction exists.
     *
     * @param string $service the service name
     *
     * @return bool
     */
    public function hasTransaction($service);

    /**
     * Clear the Circuit Breaker storage.
     *
     * @return bool
     */
    public function clear();
}
