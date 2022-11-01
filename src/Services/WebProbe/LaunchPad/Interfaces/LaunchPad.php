<?php

namespace App\Services\WebProbe\LaunchPad\Interfaces;

use App\Services\WebProbe\Missions\Interfaces\Mission;
use App\Services\WebProbe\Missions\Interfaces\MissionResult;

interface LaunchPad

{

    public function __construct(Mission $mission);

    public function launch(): MissionResult;

}