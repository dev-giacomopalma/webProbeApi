<?php

namespace App\Services\WebProbe\Probes;

use App\Services\WebProbe\Probes\Exceptions\PageLoadException;
use App\Services\WebProbe\Probes\Helpers\ScraperHelper;

class Probe
{

    /** @var string */
    private $url;

    /** @var array */
    private $preparation;

    public function __construct(string $url, array $preparation = [])
    {
        $this->url = $url;
        $this->preparation = $preparation;
    }

    /**
     * @return ProbeResult
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\EvaluationFailed
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws PageLoadException
     */
    public function run(): ProbeResult
    {

        $page = ScraperHelper::loadPage(
            $this->url,
            json_encode($this->preparation)
        );

        $probeResult = new ProbeResult();
        $probeResult->payload = ['head' => $page->getHead(), 'body' => $page->getBody()];

        return $probeResult;

    }
}
