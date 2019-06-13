<?php

namespace Tests\PrestaShop\CircuitBreaker\Transaction;

use DateTime;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Transaction\SimpleTransaction;

class SimpleTransactionTest extends TestCase
{
    public function testCreation()
    {
        $placeStub = $this->createPlaceStub();

        $simpleTransaction = new SimpleTransaction(
            'http://some-uri.domain',
            0,
            $placeStub->getState(),
            2
        );

        $this->assertInstanceOf(SimpleTransaction::class, $simpleTransaction);
    }

    /**
     * @depends testCreation
     */
    public function testGetService()
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame('http://some-uri.domain', $simpleTransaction->getService());
    }

    /**
     * @depends testCreation
     */
    public function testGetFailures()
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame(0, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testGetState()
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame('FAKE_STATE', $simpleTransaction->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetThresholdDateTime()
    {
        $simpleTransaction = $this->createSimpleTransaction();
        $expectedDateTime = (new DateTime('+2 second'))->format('d/m/Y H:i:s');
        $simpleTransactionDateTime = $simpleTransaction->getThresholdDateTime()->format('d/m/Y H:i:s');

        $this->assertSame($expectedDateTime, $simpleTransactionDateTime);
    }

    /**
     * @depends testCreation
     * @depends testGetFailures
     */
    public function testIncrementFailures()
    {
        $simpleTransaction = $this->createSimpleTransaction();
        $simpleTransaction->incrementFailures();

        $this->assertSame(1, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testCreationFromPlaceHelper()
    {
        $simpleTransactionFromHelper = SimpleTransaction::createFromPlace(
            $this->createPlaceStub(),
            'http://some-uri.domain'
        );

        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame($simpleTransactionFromHelper->getState(), $simpleTransaction->getState());
        $this->assertSame($simpleTransactionFromHelper->getFailures(), $simpleTransaction->getFailures());
        $fromPlaceDate = $simpleTransactionFromHelper->getThresholdDateTime()->format('d/m/Y H:i:s');
        $expectedDate = $simpleTransaction->getThresholdDateTime()->format('d/m/Y H:i:s');

        $this->assertSame($fromPlaceDate, $expectedDate);
    }

    /**
     * Returns an instance of SimpleTransaction for tests.
     *
     * @return SimpleTransaction
     */
    private function createSimpleTransaction()
    {
        $placeStub = $this->createPlaceStub();

        return new SimpleTransaction(
            'http://some-uri.domain',
            0,
            $placeStub->getState(),
            2
        );
    }

    /**
     * Returns an instance of Place with State equals to "FAKE_STATE"
     * and threshold equals to 2.
     *
     * @return PlaceInterface&PHPUnit_Framework_MockObject_MockObject
     */
    private function createPlaceStub()
    {
        $placeStub = $this->createMock(PlaceInterface::class);

        $placeStub->expects($this->any())
            ->method('getState')
            ->willReturn('FAKE_STATE')
        ;

        $placeStub->expects($this->any())
            ->method('getThreshold')
            ->willReturn(2)
        ;

        return $placeStub;
    }
}
