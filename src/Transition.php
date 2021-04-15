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
 * Define the available transitions of the Circuit Breaker;.
 */
final class Transition
{
    /**
     * Happened only once when calling the Circuit Breaker.
     */
    const INITIATING_TRANSITION = 'INITIATING';

    /**
     * Happened when we open the Circuit Breaker.
     * This means once the Circuit Breaker is in failure.
     */
    const OPENING_TRANSITION = 'OPENING';

    /**
     * Happened once the conditions of retry are met
     * in OPEN state to move to HALF_OPEN state in the
     * Circuit Breaker.
     */
    const CHECKING_AVAILABILITY_TRANSITION = 'CHECKING AVAILABILITY';

    /**
     * Happened when we come back to OPEN state
     * in the Circuit Breaker from the HALF_OPEN state.
     */
    const REOPENING_TRANSITION = 'REOPENING';

    /**
     * Happened if the service is available again.
     */
    const CLOSING_TRANSITION = 'CLOSING';

    /**
     * Happened on each try to call the service.
     */
    const TRIAL_TRANSITION = 'TRIAL';
}
