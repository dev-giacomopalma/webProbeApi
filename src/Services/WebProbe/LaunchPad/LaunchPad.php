<?php

namespace App\Services\WebProbe\LaunchPad;

use App\Services\WebProbe\Missions\Mission;
use App\Services\WebProbe\Missions\MissionResult;

class LaunchPad
{

    /** @var Mission */
    private $mission;

    public function __construct(Mission $mission)
    {
        $this->mission = $mission;
    }

    public function launch(): MissionResult
    {
        return $this->getMission()->execute();
    }

    public function getMission(): Mission
    {
        return $this->mission;
    }
}