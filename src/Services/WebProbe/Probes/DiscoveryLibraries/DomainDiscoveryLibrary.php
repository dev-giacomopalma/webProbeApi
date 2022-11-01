<?php


namespace App\Services\WebProbe\Probes\DiscoveryLibraries;


class DomainDiscoveryLibrary extends DiscoveryLibrary
{
    /**
     * @param string $url
     * @return string
     */
    public function findDomain(string $url): string
    {
        return parse_url($url, PHP_URL_HOST);
    }

    public function findMainDomain(string $url): string
    {
        $parts = explode('.', $this->findDomain($url));
        return $parts[1];
    }
}