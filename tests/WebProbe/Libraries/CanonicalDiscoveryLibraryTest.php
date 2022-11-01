<?php

namespace App\Tests\WebProbe\Libraries;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\DiscoveryLibraries\CanonicalDiscoveryLibrary;

class CanonicalDiscoveryLibraryTest extends TestCase
{

    const EXPECTED_CANONICAL = 'https://www.amazon.de/-/en/Aromantom-Solid-Cologne-0-5oz-Ocean/dp/B079J1CQ2Z';
    const EXPECTED_OG_URL = 'http://en.zalando.de/tamaris-classic-heels-roserose-metallic-ta111b170-j11.html';

    /** @var CanonicalDiscoveryLibrary */
    private $amazonDiscoveryLibrary;

    /** @var CanonicalDiscoveryLibrary */
    private $zalandoDiscoveryLibrary;

    public function setUp()
    {
        $this->amazonDiscoveryLibrary = new CanonicalDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/amazonProductPage.html')
        );
        $this->zalandoDiscoveryLibrary = new CanonicalDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/zalandoProductPage.html')
        );
    }

    public function testSuccessFindCanonical() {
        $canonical = $this->amazonDiscoveryLibrary->findCanonical();
        $this->assertEquals(self::EXPECTED_CANONICAL, $canonical);
    }

    public function testSuccessfullFindOgUrl() {
        $ogUrl = $this->zalandoDiscoveryLibrary->findOgFullUrl();
        $this->assertEquals(self::EXPECTED_OG_URL, $ogUrl);
    }

    public function testNotSuccessFindCanonical()
    {

        $canonical = $this->zalandoDiscoveryLibrary->findCanonical();
        $this->assertNull($canonical);
    }

    public function testNotSuccessFindOgUrl()
    {
        $ogUrl = $this->amazonDiscoveryLibrary->findOgFullUrl();
        $this->assertNull($ogUrl);
    }


}