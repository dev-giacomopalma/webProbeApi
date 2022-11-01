<?php

namespace App\Services\WebProbe\Missions\Interfaces;

use App\Services\WebProbe\Missions\Settings\MissionSetting;
use App\Services\WebProbe\Probes\Interfaces\Probe;

interface Mission
{

    public function __construct(MissionSetting $missionSetting, Probe $probe);

    public function execute(): MissionResult;

    public function getSettings(): MissionSetting;

    public function getProbe(): Probe;

}