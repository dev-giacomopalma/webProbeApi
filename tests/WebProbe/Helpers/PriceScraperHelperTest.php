<?php

namespace App\Tests\WebProbe\Helpers;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\Helpers\PriceScraperHelper;

class PriceScraperHelperTest extends TestCase
{
    public const PRICE_SPAN_STRING = '<span id="priceblock_ourprice">€329.00</span>';
    public const EMPTY_PRICE_SPAN_STRING = '<span id="priceblock_ourprice"></span>';
    public const IDENTIFIER_OK = 'id="priceblock_ourprice"';


    /**
     * @dataProvider stringPriceSpanProvider
     * @param string $page
     * @param string $identifier
     * @param string $expected
     */
    public function testGetStringPriceBySpanIdentifier(string $page, string $identifier, string $expected): void
    {
        $this->assertEquals($expected, PriceScraperHelper::getStringPriceBySpanIdentifier($page, $identifier));
    }

    /**
     * @dataProvider intPriceSpanProvider
     * @param string $page
     * @param string $identifier
     * @param string $expected
     */
    public function testGetIntegerPriceBySpanIdentifier(string $page, string $identifier, string $expected): void
    {
        $this->assertEquals($expected, PriceScraperHelper::getIntegerPriceBySpanIdentifier($page, $identifier));
    }

    public function testTypePriceBySpanIdentifier(): void
    {
        $this->assertIsInt(
          PriceScraperHelper::getIntegerPriceBySpanIdentifier(
              self::PRICE_SPAN_STRING,
              self::IDENTIFIER_OK)
        );
    }

    public function testWrongSeparatorPriceBySpanIdentifier(): void
    {
        $this->assertNull(
            PriceScraperHelper::getIntegerPriceBySpanIdentifier(
                self::PRICE_SPAN_STRING,
                self::IDENTIFIER_OK,
                ',')
        );
    }

    public function testDecimalLengthPriceBySpanIdentifier(): void
    {
        $this->assertEquals(
            5,
            floor(log10(
            PriceScraperHelper::getIntegerPriceBySpanIdentifier(
                self::PRICE_SPAN_STRING,
                self::IDENTIFIER_OK,
                '.',
                2)) + 1)
        );
    }

    public function stringPriceSpanProvider(): array
    {
        return [
            '#0 valid price' => [
                self::PRICE_SPAN_STRING,
                self::IDENTIFIER_OK,
                '329.00'
            ],
            '#1 empty price' => [
                self::EMPTY_PRICE_SPAN_STRING,
                self::IDENTIFIER_OK,
                ''
            ],
        ];
    }

    public function intPriceSpanProvider(): array
    {
        return [
            '#0 valid price' => [
                self::PRICE_SPAN_STRING,
                'id="priceblock_ourprice"',
                32900
            ],
            '#1 empty price' => [
                self::EMPTY_PRICE_SPAN_STRING,
                'id="priceblock_ourprice"',
                ''
            ],
        ];
    }
}