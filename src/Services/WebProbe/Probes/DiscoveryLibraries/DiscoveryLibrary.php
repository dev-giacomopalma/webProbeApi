<?php

namespace App\Services\WebProbe\Probes\DiscoveryLibraries;

use App\Services\WebProbe\Probes\Exceptions\ScrapeElementNotFound;
use App\Services\WebProbe\Probes\Helpers\ScraperHelper;

class DiscoveryLibrary
{

    /**
     * Read after an identifier and then inside two delimiter
     *
     * @param string $body
     * @param string $afterDelimiter
     * @param string $betweenLeftDelimiter
     * @param string $betweenRightDelimiter
     * @param bool $fullList
     * @param bool $strict
     * @return array|null
     * @throws ScrapeElementNotFound
     */
    public function readAfterAndBetween(
        string $body,
        string $afterDelimiter,
        string $betweenLeftDelimiter,
        string $betweenRightDelimiter,
        bool $fullList = false,
        bool $strict = false
    ):? array {
        try {
            $elements = ScraperHelper::readAfter($afterDelimiter, $body, $fullList, $strict);
            $vals = [];
            $insideVals = [];
            foreach ($elements as $element) {
                $insideVals[] = $this->readBetween($betweenLeftDelimiter, $betweenRightDelimiter, $element, false, $strict);
            }

            foreach ($insideVals as $insideVal) {
                $vals[] = $insideVal[0];
            }

            return $vals;
        } catch (ScrapeElementNotFound $exception) {
            throw $exception;
        }
    }

    /**
     * Read inside two delimiter and with the result received, read before an identifier
     *
     * @param string $body
     * @param string $betweenLeftDelimiter
     * @param string $betweenRightDelimiter
     * @param string $beforeDelimiter
     * @param bool $fullList
     * @param bool $strict
     * @return array|null
     * @throws ScrapeElementNotFound
     */
    public function readBetweenAndBefore(
        string $body,
        string $betweenLeftDelimiter,
        string $betweenRightDelimiter,
        string $beforeDelimiter,
        bool $fullList = false,
        bool $strict = false
    ):? array {
        try {
            $vals = [];
            $insideVals = [];
            $elements = $this->readBetween(
                $betweenLeftDelimiter,
                $betweenRightDelimiter,
                $body,
                $fullList,
                $strict);
            foreach ($elements as $element) {
                $insideVals[] = ScraperHelper::readBefore($beforeDelimiter, trim($element), $strict);
            }

            foreach ($insideVals as $insideVal) {
                if (isset($insideVal[0])) {
                    $vals[] = $insideVal[0];
                }
            }

            return $vals;
        } catch (ScrapeElementNotFound $exception) {
            throw $exception;
        }
    }

    /**
     * Read between two delimiters
     *
     * @param string $betweenLeftDelimiter
     * @param string $betweenRightDelimiter
     * @param string $body
     * @param bool $fullList
     * @param bool $strict
     * @return array
     * @throws ScrapeElementNotFound
     */
    public function readBetween(
        string $betweenLeftDelimiter,
        string $betweenRightDelimiter,
        string $body,
        bool $fullList = false,
        bool $strict = false
    ): array {
        return ScraperHelper::readBetween($betweenLeftDelimiter, $betweenRightDelimiter, $body, $fullList, $strict);
    }

}