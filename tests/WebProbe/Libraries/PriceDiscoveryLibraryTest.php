<?php

namespace App\Tests\WebProbe\Libraries;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\DiscoveryLibraries\PriceDiscoveryLibrary;

class PriceDiscoveryLibraryTest extends TestCase
{
    const URL = 'https://en.zalando.de/tommy-hilfiger-chronograph-watch-silberfarbenblau-to152m01a-k11.html';
    const EXPECTED_PRICE = '4855';


    /** @var PriceDiscoveryLibrary */
    private $priceDiscoveryLibrary;

    public function setUp()
    {
        $this->priceDiscoveryLibrary = new PriceDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/zalandoProductPage.html')
        );
    }

    public function testFindPrice() {
        $this->assertEquals(self::EXPECTED_PRICE, $this->priceDiscoveryLibrary->findPrice()->getValue());
    }
}