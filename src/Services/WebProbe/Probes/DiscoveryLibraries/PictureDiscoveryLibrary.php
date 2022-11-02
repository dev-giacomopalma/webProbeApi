<?php

namespace App\Services\WebProbe\Probes\DiscoveryLibraries;

use App\Services\WebProbe\Probes\Exceptions\ScrapeElementNotFound;

class PictureDiscoveryLibrary extends DiscoveryLibrary
{
    /** @var string */
    private $page;

    public function __construct(string $page)
    {
        $this->page = $page;
    }

    /**
     * Return the image url contained in the content of og:image (if present)
     *
     * @return string|null
     */
    public function findOgImage():? string
    {
        try {
            $elements = $this->readBetween(
                '"og:image" content="',
                '"',
                $this->page,
                false,
                true
            );

            if (isset($elements[0])) {
                return $this->cleanImgUrl($elements[0]);
            } else {
                return null;
            }
        } catch (ScrapeElementNotFound $exception) {
            return null;
        }
    }

    private function cleanImgUrl(string $url): string
    {
        $elements = explode('?',$url);
        return $elements[0] ?? $url;
    }

}