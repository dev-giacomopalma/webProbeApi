<?php

namespace App\Tests\WebProbe\Libraries;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\DiscoveryLibraries\DomainDiscoveryLibrary;

class DomainDiscoveryLibraryTest extends TestCase
{

    const URL = 'https://en.zalando.de/tamaris-classic-heels-roserose-metallic-ta111b170-j11.html';
    const EXPECTED_DOMAIN = 'en.zalando.de';
    const EXPECTED_MAIN_DOMAIN = 'zalando';


    /** @var DomainDiscoveryLibrary */
    private $domainDiscoveryLibrary;

    public function setUp()
    {
        $this->domainDiscoveryLibrary = new DomainDiscoveryLibrary();
    }

    public function testFindDomain() {
        $this->assertEquals(
            self::EXPECTED_DOMAIN,
            $this->domainDiscoveryLibrary->findDomain(self::URL)
        );

        $this->assertEquals(
            self::EXPECTED_MAIN_DOMAIN,
            $this->domainDiscoveryLibrary->findMainDomain(self::URL)
        );
    }

}