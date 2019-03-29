<?php

namespace PrestaShop\CircuitBreaker\Contracts;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFoundException;

/**
 * Store the transaction between the Circuit Breaker
 * and the tiers service.
 */
interface Storage
{
    /**
     * Save the CircuitBreaker transaction.
     *
     * @param string $service The service name
     * @param Transaction $transaction the transaction
     *
     * @return bool
     */
    public function saveTransaction($service, Transaction $transaction);

    /**
     * Retrieve the CircuitBreaker transaction for a specific service.
     *
     * @param string $service the service name
     *
     * @return Transaction
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
