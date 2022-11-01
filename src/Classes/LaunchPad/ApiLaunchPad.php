<?php

namespace App\Classes\LaunchPad;

use App\Services\WebProbe\LaunchPad\LaunchPad;
use App\Services\WebProbe\Missions\Interfaces\MissionResult;

class ApiLaunchPad extends LaunchPad
{

    public function launch(): MissionResult
    {
        return $this->getMission()->execute();
    }
}
