<?php

namespace App\Services\WebProbe\Probes\Helpers;

class PriceScraperHelper extends ScraperHelper
{

    public static function getStringPriceBySpanIdentifier(string $page, string $identifier): string
    {
        return trim(self::removeCurrenciesSign(self::readSpanByUniqueStart($page, $identifier)));
    }

    public static function getIntegerPriceBySpanIdentifier(
        string $page,
        string $identifier,
        string $decimalDelimiter = '.',
        int $precision = 2):? int
    {
        $stringPrice = self::getStringPriceBySpanIdentifier($page, $identifier);
        $blocks = explode($decimalDelimiter, $stringPrice);
        if (count($blocks) > 1) {
            return (int) $blocks[0].substr($blocks[1],0,$precision);
        }

        return null;
    }

}