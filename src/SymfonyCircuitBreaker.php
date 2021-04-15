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

use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\SystemInterface;
use PrestaShop\CircuitBreaker\Transition\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Symfony implementation of Circuit Breaker.
 */
final class SymfonyCircuitBreaker extends AdvancedCircuitBreaker
{
    /**
     * @var EventDispatcherInterface the Symfony Event Dispatcher
     */
    private $eventDispatcher;

    public function __construct(
        SystemInterface $system,
        ClientInterface $client,
        StorageInterface $storage,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct($system, $client, $storage, new EventDispatcher($eventDispatcher));
    }
}
