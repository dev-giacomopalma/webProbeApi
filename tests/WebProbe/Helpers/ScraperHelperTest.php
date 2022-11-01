<?php

namespace App\Tests\WebProbe\Helpers;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\Helpers\ScraperHelper;

class ScraperHelperTest extends TestCase
{
    public const PRICE_SPAN_STRING = 'loremipsumdolorem<span id="priceblock_ourprice" class="a-size-medium a-color-price priceBlockBuyingPriceString">€329.00</span>loremipsumdolorem';
    public const EMPTY_PRICE_SPAN_STRING = 'loremipsumdolorem<span id="priceblock_ourprice" class="a-size-medium a-color-price priceBlockBuyingPriceString"></span>loremipsumdolorem';


    /**
     * @dataProvider spanProvider
     * @param string $page
     * @param string $identifier
     * @param string $expected
     */
    public function testReadSpanByUniqueStart(string $page, string $identifier, string $expected): void
    {
        $this->assertEquals($expected, ScraperHelper::readSpanByUniqueStart($page,$identifier));
    }


    public function spanProvider(): array
    {
        return [
            '#0 test succesfull' => [
                self::PRICE_SPAN_STRING,
                'id="priceblock_ourprice"',
                '€329.00'
            ],
            '#1 test empty' => [
                self::EMPTY_PRICE_SPAN_STRING,
                'id="priceblock_ourprice"',
                ''
            ],
            '#2 test wrong identifier' => [
                self::PRICE_SPAN_STRING,
                'id="priceblock_ourprice_wrong"',
                ''
            ],
        ];
    }

}