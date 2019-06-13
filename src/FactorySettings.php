<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransitionDispatcherInterface;

/**
 * Class FactorySettings is a simple implementation of FactorySettingsInterface, it is mainly
 * a settings container and can be used with any Factory class.
 */
class FactorySettings implements FactorySettingsInterface
{
    /** @var int */
    private $failures;

    /** @var float */
    private $timeout;

    /** @var int */
    private $threshold;

    /** @var float */
    private $strippedTimeout;

    /** @var int */
    private $strippedFailures;

    /** @var StorageInterface|null */
    private $storage;

    /** @var TransitionDispatcherInterface|null */
    private $dispatcher;

    /** @var array */
    private $clientOptions = [];

    /** @var ClientInterface|null */
    private $client;

    /** @var callable|null */
    private $defaultFallback;

    /**
     * @param int $failures
     * @param float $timeout
     * @param int $threshold
     */
    public function __construct(
        $failures,
        $timeout,
        $threshold
    ) {
        $this->failures = $this->strippedFailures = $failures;
        $this->timeout = $this->strippedTimeout = $timeout;
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    public static function merge(FactorySettingsInterface $settingsA, FactorySettingsInterface $settingsB)
    {
        $mergedSettings = new FactorySettings(
            $settingsB->getFailures(),
            $settingsB->getTimeout(),
            $settingsB->getThreshold()
        );
        $mergedSettings
            ->setStrippedFailures($settingsB->getStrippedFailures())
            ->setStrippedTimeout($settingsB->getStrippedTimeout())
        ;

        $mergedSettings->setClientOptions(array_merge(
            $settingsA->getClientOptions(),
            $settingsB->getClientOptions()
        ));

        if (null !== $settingsB->getClient()) {
            $mergedSettings->setClient($settingsB->getClient());
        } elseif (null !== $settingsA->getClient()) {
            $mergedSettings->setClient($settingsA->getClient());
        }

        return $mergedSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * @param int $failures
     *
     * @return FactorySettings
     */
    public function setFailures($failures)
    {
        $this->failures = $failures;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     *
     * @return FactorySettings
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * @param int $threshold
     *
     * @return FactorySettings
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStrippedTimeout()
    {
        return $this->strippedTimeout;
    }

    /**
     * @param float $strippedTimeout
     *
     * @return FactorySettings
     */
    public function setStrippedTimeout($strippedTimeout)
    {
        $this->strippedTimeout = $strippedTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getStrippedFailures()
    {
        return $this->strippedFailures;
    }

    /**
     * @param int $strippedFailures
     *
     * @return FactorySettings
     */
    public function setStrippedFailures($strippedFailures)
    {
        $this->strippedFailures = $strippedFailures;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     *
     * @return FactorySettings
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param TransitionDispatcherInterface $dispatcher
     *
     * @return FactorySettings
     */
    public function setDispatcher(TransitionDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions()
    {
        return $this->clientOptions;
    }

    /**
     * @param array $clientOptions
     *
     * @return FactorySettings
     */
    public function setClientOptions(array $clientOptions)
    {
        $this->clientOptions = $clientOptions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface|null $client
     *
     * @return FactorySettings
     */
    public function setClient(ClientInterface $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFallback()
    {
        return $this->defaultFallback;
    }

    /**
     * @param callable $defaultFallback
     *
     * @return FactorySettings
     */
    public function setDefaultFallback(callable $defaultFallback)
    {
        $this->defaultFallback = $defaultFallback;

        return $this;
    }
}
