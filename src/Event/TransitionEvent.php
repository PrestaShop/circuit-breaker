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

namespace PrestaShop\CircuitBreaker\Event;

use Symfony\Component\EventDispatcher\Event;

class TransitionEvent extends Event
{
    /**
     * @var string the Transition name
     */
    private $eventName;

    /**
     * @var string the Service URI
     */
    private $service;

    /**
     * @var array the Service parameters
     */
    private $parameters;

    /**
     * @param string $eventName the transition name
     * @param string $service the Service URI
     * @param array $parameters the Service parameters
     */
    public function __construct(string $eventName, string $service, array $parameters)
    {
        $this->eventName = $eventName;
        $this->service = $service;
        $this->parameters = $parameters;
    }

    /**
     * @return string the Transition name
     */
    public function getEvent(): string
    {
        return $this->eventName;
    }

    /**
     * @return string the Service URI
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return array the Service parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
