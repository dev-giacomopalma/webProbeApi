<?php

namespace App\Services\WebProbe\Probes\DiscoveryLibraries;

use App\Services\WebProbe\Probes\Exceptions\ScrapeElementNotFound;

class CanonicalDiscoveryLibrary extends DiscoveryLibrary
{
    /** @var string */
    private $page;

    public function __construct(string $page)
    {
        $this->page = $page;
    }

    /**
     * Return the value contained in the href of the rel="canonical" in the page (if present)
     *
     * @return string|null
     */
    public function findCanonical():? string
    {
        try {
            $element = $this->readAfterAndBetween(
                $this->page,
                'rel="canonical"',
                'href="',
                '"',
                false,
                true
            );

            return $element[0];
        } catch (ScrapeElementNotFound $exception) {
            return null;
        }
    }

    /**
     * Return the concatenation of og:site and og:url (if both presents)
     *
     * @return string|null
     */
    public function findOgFullUrl():? string
    {
        $ogSite = $this->findOgSite();
        $ogUrl = $this->findOgUrl();

        if (null === $ogSite || null === $ogUrl) {
            return null;
        }

        if (substr($ogSite, -1) === '/' && substr($ogUrl, 0, 1) === '/') {
            $ogUrl = substr($ogUrl, 1);
        }

        return $ogSite.$ogUrl;
    }

    /**
     * Return the url contained in the content of og:url (if present)
     *
     * @return string|null
     */
    public function findOgUrl():? string
    {
        try {
            $elements =  $this->readAfterAndBetween(
                $this->page,
                'property="og:url"',
                'content="',
                '"',
                false,
                true
            );

            return $elements[0];
        } catch (ScrapeElementNotFound $exception) {
            return null;
        }
    }

    /**
     * Return the site url contained in the content of og:site (if present)
     *
     * @return string|null
     */
    public function findOgSite():? string
    {
        try {
            $elements = $this->readAfterAndBetween(
                $this->page,
                'property="og:site"',
                'content="',
                '"',
                false,
                true
            );

            return $elements[0];
        } catch (ScrapeElementNotFound $exception) {
            return null;
        }
    }
}