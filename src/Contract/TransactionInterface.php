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

namespace PrestaShop\CircuitBreaker\Contract;

use DateTime;

/**
 * Once the circuit breaker call a service,
 * a transaction is initialized and stored.
 */
interface TransactionInterface
{
    /**
     * @return string the service name
     */
    public function getService(): string;

    /**
     * @return int the number of failures to call the service
     */
    public function getFailures(): int;

    /**
     * @return string the current state of the Circuit Breaker
     */
    public function getState(): string;

    /**
     * @return DateTime the time when the circuit breaker move
     *                  from open to half open state
     */
    public function getThresholdDateTime(): DateTime;

    /**
     * Everytime the service call fails, increment the number of failures.
     */
    public function incrementFailures(): bool;
}
