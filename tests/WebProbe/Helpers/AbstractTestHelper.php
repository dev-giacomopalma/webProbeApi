<?php

namespace App\Tests\WebProbe\Helpers;

abstract class AbstractTestHelper
{

    public const PRICE_SPAN_STRING = 'loremipsumdolorem<span id="priceblock_ourprice" class="a-size-medium a-color-price priceBlockBuyingPriceString">€329.00</span>loremipsumdolorem';
    public const EMPTY_PRICE_SPAN_STRING = 'loremipsumdolorem<span id="priceblock_ourprice" class="a-size-medium a-color-price priceBlockBuyingPriceString"></span>loremipsumdolorem';

}