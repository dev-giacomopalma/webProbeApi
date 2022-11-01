<?php

namespace App\Services\WebProbe\Probes\Interfaces;

use App\Services\WebProbe\Probes\ProbeResult;
use App\Services\WebProbe\Probes\Settings\ProbeSetting;

interface Probe
{

    public function __construct(ProbeSetting $probeSetting);

    public function run(): ProbeResult;

}