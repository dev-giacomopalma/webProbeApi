<?php

namespace App\Classes\Probe;

use App\Classes\Probe\Setting\ApiProbeSetting;
use twittingeek\webProbe\Probes\Helpers\ScraperHelper;
use twittingeek\webProbe\Probes\Interfaces\Probe;
use twittingeek\webProbe\Probes\ProbeResult;
use twittingeek\webProbe\Probes\Settings\ProbeSetting;

class ApiProbe implements Probe
{

    /** @var ProbeSetting */
    private $probeSetting;

    public function __construct(ProbeSetting $probeSetting)
    {
        $this->probeSetting = $probeSetting;
    }

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