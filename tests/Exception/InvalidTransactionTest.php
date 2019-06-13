<?php

namespace Tests\PrestaShop\CircuitBreaker\Exception;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Exception\InvalidTransactionException;

class InvalidTransactionTest extends TestCase
{
    public function testCreation()
    {
        $invalidPlace = new InvalidTransactionException();

        $this->assertInstanceOf(InvalidTransactionException::class, $invalidPlace);
    }

    /**
     * @dataProvider getParameters
     *
     * @param array $parameters
     * @param string $expectedExceptionMessage
     */
    public function testInvalidParameters($parameters, $expectedExceptionMessage)
    {
        $invalidPlace = InvalidTransactionException::invalidParameters(
            $parameters[0], // service
            $parameters[1], // failures
            $parameters[2], // state
            $parameters[3]  // threshold
        );

        $this->assertSame($invalidPlace->getMessage(), $expectedExceptionMessage);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return [
            'all_invalid_parameters' => [
                [100, '0', null, 'toto'],
                'Invalid parameters for Transaction' . PHP_EOL .
                'Excepted service to be an URI, got integer (100)' . PHP_EOL .
                'Excepted failures to be a positive integer, got string (0)' . PHP_EOL .
                'Excepted state to be a string, got NULL' . PHP_EOL .
                'Excepted threshold to be a positive integer, got string (toto)' . PHP_EOL,
            ],
            '3_invalid_parameters' => [
                ['http://www.prestashop.com', '1', null, 'toto'],
                'Invalid parameters for Transaction' . PHP_EOL .
                'Excepted failures to be a positive integer, got string (1)' . PHP_EOL .
                'Excepted state to be a string, got NULL' . PHP_EOL .
                'Excepted threshold to be a positive integer, got string (toto)' . PHP_EOL,
            ],
            '2_invalid_parameters' => [
                ['http://www.prestashop.com', 10, null, null],
                'Invalid parameters for Transaction' . PHP_EOL .
                'Excepted state to be a string, got NULL' . PHP_EOL .
                'Excepted threshold to be a positive integer, got NULL' . PHP_EOL,
            ],
            'none_invalid' => [
                ['http://www.prestashop.com', 10, 'CLOSED_STATE', 1],
                'Invalid parameters for Transaction' . PHP_EOL,
            ],
        ];
    }
}
