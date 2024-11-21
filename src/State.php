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

/**
 * Define the available states of the Circuit Breaker;.
 */
final class State
{
    /**
     * Once opened, a circuit breaker doesn't do any call
     * to third-party services. Only the alternative call is done.
     */
    public const OPEN_STATE = 'OPEN';

    /**
     * After some conditions are valid, the circuit breaker
     * try to access the third-party service. If the service is valid,
     * the circuit breaker go to CLOSED state. If it's not, the circuit breaker
     * go to OPEN state.
     */
    public const HALF_OPEN_STATE = 'HALF OPEN';

    /**
     * On the first call of the service, or if the service is valid
     * the circuit breaker is in CLOSED state. This means that the callable
     * to evaluate is done and not the alternative call.
     */
    public const CLOSED_STATE = 'CLOSED';
}
