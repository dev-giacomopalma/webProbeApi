<?php

namespace App\Tests\WebProbe\Libraries;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\Helpers\ScraperHelper;
use App\Services\WebProbe\Probes\DiscoveryLibraries\PriceDiscoveryLibrary;

class PriceDiscoveryLibraryTest extends TestCase
{
    const URL = 'https://en.zalando.de/tamaris-classic-heels-roserose-metallic-ta111b170-j11.html';
    const EXPECTED_PRICE = '4855';


    /** @var PriceDiscoveryLibrary */
    private $priceDiscoveryLibrary;

    /** @var PriceDiscoveryLibrary */
    private $productionDiscoveryLibrary;

    public function setUp()
    {
        $this->priceDiscoveryLibrary = new PriceDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/zalandoProductPage.html')
        );

        $page = ScraperHelper::loadPage(self::URL);
        $this->productionDiscoveryLibrary = new PriceDiscoveryLibrary(
            $page->getHead().$page->getBody()
        );
    }

    public function testFindPrice() {
        $this->assertEquals(self::EXPECTED_PRICE, $this->priceDiscoveryLibrary->findPrice());
    }

    public function findProductionPrice() {
        $this->markTestSkipped('
            to be executed only on request on live systems 
        ');
        $this->assertEquals(self::EXPECTED_PRICE, $this->productionDiscoveryLibrary->findPrice());

    }
}