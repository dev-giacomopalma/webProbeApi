<?php

namespace App\Services\WebProbe\LaunchPad;

use App\Services\WebProbe\LaunchPad\Interfaces\LaunchPad as LaunchPadInterface;
use App\Services\WebProbe\Missions\Interfaces\Mission;
use App\Services\WebProbe\Missions\Interfaces\MissionResult;


class LaunchPad implements LaunchPadInterface
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