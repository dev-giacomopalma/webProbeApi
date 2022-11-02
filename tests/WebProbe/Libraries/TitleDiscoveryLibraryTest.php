<?php

namespace App\Tests\WebProbe\Libraries;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\DiscoveryLibraries\TitleDiscoveryLibrary;

class TitleDiscoveryLibraryTest extends TestCase
{

    const EXPECTED_HTML_TITLE = 'Tamaris Classic heels - rose/rose metallic - Zalando.de';

    /** @var TitleDiscoveryLibrary */
    private $zalandoDiscoveryLibrary;

    public function setUp()
    {
        $this->zalandoDiscoveryLibrary = new TitleDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/zalandoProductPage.html')
        );
    }

    public function testSuccessFindHTMLTitle()
    {
        $htmlTitle = $this->zalandoDiscoveryLibrary->findHTMLTitle();
        $this->assertEquals(self::EXPECTED_HTML_TITLE, $htmlTitle);
    }
}