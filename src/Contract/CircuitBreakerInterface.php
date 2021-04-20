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

/**
 * A circuit breaker is used to provide
 * an alternative response when a tiers service
 * is unreachable.
 */
interface CircuitBreakerInterface
{
    /**
     * @return string the circuit breaker state
     */
    public function getState(): string;

    /**
     * The function that execute the service.
     *
     * @param string $service the service to call
     * @param array $parameters the parameters for the request
     * @param callable|null $fallback if the service is unavailable, rely on the fallback
     */
    public function call(string $service, array $parameters = [], callable $fallback = null): string;

    /**
     * @return bool checks if the circuit breaker is open
     */
    public function isOpened(): bool;

    /**
     * @return bool checks if the circuit breaker is half open
     */
    public function isHalfOpened(): bool;

    /**
     * @return bool checks if the circuit breaker is closed
     */
    public function isClosed(): bool;
}
