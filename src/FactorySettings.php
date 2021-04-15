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

    public function __construct(
        int $failures,
        float $timeout,
        int $threshold
    ) {
        $this->failures = $this->strippedFailures = $failures;
        $this->timeout = $this->strippedTimeout = $timeout;
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    public static function merge(FactorySettingsInterface $settingsA, FactorySettingsInterface $settingsB): FactorySettingsInterface
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
    public function getFailures(): int
    {
        return $this->failures;
    }

    public function setFailures(int $failures): self
    {
        $this->failures = $failures;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setTimeout(float $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function setThreshold(int $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStrippedTimeout(): float
    {
        return $this->strippedTimeout;
    }

    public function setStrippedTimeout(float $strippedTimeout): self
    {
        $this->strippedTimeout = $strippedTimeout;

        return $this;
    }

    public function getStrippedFailures(): int
    {
        return $this->strippedFailures;
    }

    public function setStrippedFailures(int $strippedFailures): self
    {
        $this->strippedFailures = $strippedFailures;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(): ?StorageInterface
    {
        return $this->storage;
    }

    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatcher(): ?TransitionDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function setDispatcher(TransitionDispatcherInterface $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions(): array
    {
        return $this->clientOptions;
    }

    public function setClientOptions(array $clientOptions): self
    {
        $this->clientOptions = $clientOptions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(): ?ClientInterface
    {
        return $this->client;
    }

    public function setClient(?ClientInterface $client = null): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFallback(): ?callable
    {
        return $this->defaultFallback;
    }

    public function setDefaultFallback(callable $defaultFallback): self
    {
        $this->defaultFallback = $defaultFallback;

        return $this;
    }
}
