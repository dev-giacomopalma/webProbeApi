<?php

namespace App\Classes\LaunchPad;

use twittingeek\webProbe\LaunchPad\LaunchPad;
use twittingeek\webProbe\Missions\Interfaces\MissionResult;

class ApiLaunchPad extends LaunchPad
{

    public function launch(): MissionResult
    {
        return $this->getMission()->execute();
    }
}
