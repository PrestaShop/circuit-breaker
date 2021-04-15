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

namespace PrestaShop\CircuitBreaker\Transaction;

use DateTime;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\InvalidTransactionException;
use PrestaShop\CircuitBreaker\Util\Assert;

/**
 * Main implementation of Circuit Breaker transaction.
 */
final class SimpleTransaction implements TransactionInterface
{
    /**
     * @var string the URI of the service
     */
    private $service;

    /**
     * @var int the failures when we call the service
     */
    private $failures;

    /**
     * @var string the Circuit Breaker state
     */
    private $state;

    /**
     * @var DateTime the Transaction threshold datetime
     */
    private $thresholdDateTime;

    /**
     * @param string $service the service URI
     * @param int $failures the allowed failures
     * @param string $state the circuit breaker state/place
     * @param int $threshold the place threshold
     */
    public function __construct(string $service, int $failures, string $state, int $threshold)
    {
        $this->validate($service, $failures, $state, $threshold);

        $this->service = $service;
        $this->failures = $failures;
        $this->state = $state;
        $this->initThresholdDateTime($threshold);
    }

    /**
     * {@inheritdoc}
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailures(): int
    {
        return $this->failures;
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getThresholdDateTime(): DateTime
    {
        return $this->thresholdDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementFailures(): bool
    {
        ++$this->failures;

        return true;
    }

    /**
     * Helper to create a transaction from the Place.
     *
     * @param PlaceInterface $place the Circuit Breaker place
     * @param string $service the service URI
     */
    public static function createFromPlace(PlaceInterface $place, string $service): self
    {
        $threshold = $place->getThreshold();

        return new self(
            $service,
            0,
            $place->getState(),
            $threshold
        );
    }

    /**
     * Set the right DateTime from the threshold value.
     *
     * @param int $threshold the Transaction threshold
     */
    private function initThresholdDateTime(int $threshold): void
    {
        $thresholdDateTime = new DateTime();
        $thresholdDateTime->modify("+$threshold second");

        $this->thresholdDateTime = $thresholdDateTime;
    }

    /**
     * Ensure the transaction is valid (PHP5 is permissive).
     *
     * @param string $service the service URI
     * @param int $failures the failures should be a positive value
     * @param string $state the Circuit Breaker state
     * @param int $threshold the threshold should be a positive value
     *
     * @return bool true if valid
     *
     * @throws InvalidTransactionException
     */
    private function validate(string $service, int $failures, string $state, int $threshold): bool
    {
        $assertionsAreValid = Assert::isURI($service)
            && Assert::isPositiveInteger($failures)
            && Assert::isString($state)
            && Assert::isPositiveInteger($threshold)
        ;

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidTransactionException::invalidParameters($service, $failures, $state, $threshold);
    }
}
