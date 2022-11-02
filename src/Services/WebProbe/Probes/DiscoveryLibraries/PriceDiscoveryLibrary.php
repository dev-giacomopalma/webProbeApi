<?php

namespace App\Services\WebProbe\Probes\DiscoveryLibraries;

use App\Services\WebProbe\Probes\DiscoveryLibraries\Dtos\Price;

class PriceDiscoveryLibrary extends DiscoveryLibrary
{
    private const ANALISE_STRING_LENGHT = 7;
    private const DEFAULT_CURRENCY = '€';
    private const CURRENCIES_REPLACE = [
        self::DEFAULT_CURRENCY => [
            '&euro;','eur','EUR','euro','euros','EURO','EUROS','\u20ac','&#8364;','&#128182;','&#x20AC;'
        ]
    ];

    /** @var string */
    private $page;

    /** @var string */
    private $currency;

    /** @var array */
    private $stack;

    public function __construct(string $page, string $currency = self::DEFAULT_CURRENCY)
    {
        $this->stack = [];
        $this->page = $page;

        if (!array_key_exists($currency, self::CURRENCIES_REPLACE)) {
            throw new Exception('Currency %s is not valid', $currency);
        }
        $this->currency = $currency;
    }

    public function findPrice(): Price
    {
        $this->preparePage();
        $allPricesFound = $this->analiseChunksForPrices(
            $this->getPageChunks()
        );

        $allPricesFound = array_values(array_filter($allPricesFound));
        $price = null;
        if (isset($allPricesFound[0]) && null !== $allPricesFound[0] && array_key_exists(0, $allPricesFound)) {
            $price = $allPricesFound[0];
        }
        return new Price($this->stack, $price);
    }

    private function preparePage(): void
    {
        $this->page = strip_tags($this->page);
        $this->page = str_replace(
            self::CURRENCIES_REPLACE[$this->currency],
            $this->currency,
            $this->page);

        $this->page = str_replace(['.',','],'', $this->page);

    }

    private function getPageChunks(): array
    {
        return explode($this->currency, $this->page);
    }

    private function analiseChunksForPrices(array $pageChunks): array
    {
        $allFound = [];
        foreach($pageChunks as $key => $chunk) {
            $this->stack[] = $chunk;
            if ($key === 0) { //only the right side counts
                $allFound[] = $this->analiseSide('right', $chunk);
            } else if ($key === count($pageChunks) - 1) { //onluy the left counts
                $allFound[] = $this->analiseSide('left', $chunk);
            } else {
                $allFound[] = $this->analiseSide('left', $chunk);
                $allFound[] = $this->analiseSide('right', $chunk);
            }
        }

        return $allFound;
    }

    private function analiseSide(string $sideDirection, string $chunk):? string
    {
        $chunk = strtr($chunk, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        $chunk = trim($chunk,chr(0xC2).chr(0xA0));
        $chunk = trim(str_replace([' ','&nbsp;'],'',$chunk),' \t\n\r');

        $portion = '';
        if (strlen($chunk) > 0) {
            switch ($sideDirection) {
                case 'right':
                    $portion = substr(
                        $chunk,
                        strlen($chunk) - self::ANALISE_STRING_LENGHT,
                        self::ANALISE_STRING_LENGHT + 1
                    );
                    break;
                case 'left':
                    $portion = substr($chunk, 0, self::ANALISE_STRING_LENGHT);
                    break;
            }
        }
        $portion = trim($portion);
        $this->stack[] = sprintf('Portion: %s', $portion);

        if ($portion === '') {
            return null;
        }


        $sideToArray = str_split($portion);
        if ($sideDirection === 'right') {
            $sideToArray = array_reverse($sideToArray);
        }

        $found = $this->getNumericSequence($sideToArray);

        if (strlen($found) > 2) { // I assume a price is at least 3 numbers
            $foundToArray = str_split($found);
            if ($sideDirection === 'right') {
                $foundToArray = array_reverse($foundToArray);
            }
            $finalFound = '';

            foreach ($foundToArray as $c) {
                $finalFound .= $c;
            }
            return $finalFound;
        }
        return null;
    }

    private function getNumericSequence(array $array): string
    {
        $found = '';
        foreach ($array as $key => $val) {
            if ($key === 0 || $key === count($array) -1 ) {
                if (is_numeric($val)) {
                    $found .= $val;
                }
            } else if (is_numeric($val) && is_numeric($array[$key] -1)) {
                $found .= $val;
            }
        }
        return $found;
    }

}