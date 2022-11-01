<?php

namespace App\Tests\WebProbe\Libraries;

use PHPUnit\Framework\TestCase;
use App\Services\WebProbe\Probes\DiscoveryLibraries\PictureDiscoveryLibrary;

class PictureDiscoveryLibraryTest extends TestCase {

    const EXPECTED_OG_IMAGE = 'https://img01.ztat.net/article/TA/11/1B/17/0J/11/TA111B170-J11@6.jpg';

    /** @var PictureDiscoveryLibrary */
    private $amazonDiscoveryLibrary;

    /** @var PictureDiscoveryLibrary */
    private $zalandoDiscoveryLibrary;

    public function setUp()
    {
        $this->amazonDiscoveryLibrary = new PictureDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/amazonProductPage.html')
        );
        $this->zalandoDiscoveryLibrary = new PictureDiscoveryLibrary(
            file_get_contents(__DIR__.'/TestFiles/zalandoProductPage.html')
        );
    }

    public function testSuccessFindOgImage()
    {
        $ogImage = $this->zalandoDiscoveryLibrary->findOgImage();
        $this->assertEquals(self::EXPECTED_OG_IMAGE, $ogImage);
    }

    public function testNotSuccessFindOgImage()
    {
        $ogImage = $this->amazonDiscoveryLibrary->findOgImage();
        $this->assertNull($ogImage);
    }


}