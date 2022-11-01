<?php

namespace App\Classes\Probe;

use App\Services\WebProbe\Probes\Settings\ProbeSetting;
use Exception;
use App\Services\WebProbe\Probes\Exceptions\PageLoadException;
use App\Services\WebProbe\Probes\Helpers\ScraperHelper;
use App\Services\WebProbe\Probes\Interfaces\Probe;
use App\Services\WebProbe\Probes\ProbeResult;

class ApiProbe implements Probe
{

    /** @var ProbeSetting */
    private $probeSetting;

    public function __construct(ProbeSetting $probeSetting)
    {
        $this->probeSetting = $probeSetting;
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
            $this->probeSetting->getUrl(),
            json_encode($this->probeSetting->getPreparation())
        );

        $probeResult = new ProbeResult();
        $probeResult->payload = ['head' => $page->getHead(), 'body' => $page->getBody()];

        return $probeResult;

    }
}
