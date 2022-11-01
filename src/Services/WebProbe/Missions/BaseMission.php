<?php

namespace App\Services\WebProbe\Missions;

use App\Services\WebProbe\Missions\Interfaces\Mission;
use App\Services\WebProbe\Missions\Interfaces\MissionResult;
use App\Services\WebProbe\Missions\Settings\MissionSetting;
use App\Services\WebProbe\Probes\Interfaces\Probe;

abstract class BaseMission implements Mission
{

    /** @var MissionSetting */
    public $missionSetting;

    /** @var Probe */
    public $probe;

    public function __construct(MissionSetting $missionSetting, Probe $probe)
    {
        $this->missionSetting = $missionSetting;
        $this->probe = $probe;
    }

    abstract public function execute(): MissionResult;

    public function getSettings(): MissionSetting
    {
        return $this->missionSetting;
    }

    public function getProbe(): Probe
    {
        return $this->probe;
    }
}