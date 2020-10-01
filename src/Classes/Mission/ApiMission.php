<?php


namespace App\Classes\Mission;


use App\Classes\Mission\Dto\FieldDto;
use twittingeek\webProbe\Missions\BaseMission;
use twittingeek\webProbe\Missions\Interfaces\MissionResult;
use twittingeek\webProbe\Missions\Settings\MissionSetting;
use twittingeek\webProbe\Probes\Helpers\ScraperHelper;
use twittingeek\webProbe\Probes\Interfaces\Probe;
use twittingeek\webProbe\Probes\Libraries\DiscoveryLibrary;
use twittingeek\webProbe\Probes\ProbeResult;

class ApiMission extends BaseMission
{

    /** @var ProbeResult */
    private $probeResult;

    /** @var DiscoveryLibrary */
    private $discoveryLibrary;

    public function execute(): MissionResult
    {
        $this->probeResult = $this->probe->run();
        if (null !== $this->missionSetting->getEvaluation() && !empty($this->missionSetting->getEvaluation())) {
            $this->discoveryLibrary = new DiscoveryLibrary();
            $res = [];
            /** @var array $evaluation */
            foreach ($this->missionSetting->getEvaluation() as $evaluationRules) {
                $resEvaluation = [];
                foreach ($evaluationRules as $name => $evaluationRule) {
                    $field = new FieldDto();
                    $field->name = $name;
                    switch ($evaluationRule['type']) {
                        case "tag":
                            $field->value = $this->evaluateFieldTag($evaluationRule);
                            break;
                        case "text":
                            $field->value = $this->evaluateFieldText($evaluationRule);
                            break;
                        default:
                            //todo throw unsupported type exception
                            break;
                    }
                    $res[] = $field;
                }
            }
            return new MissionResult(MissionResult::OK_STATUS_CODE, $res);

        }

        return new MissionResult(MissionResult::OK_STATUS_CODE, $this->probeResult->payload);

    }

    private function evaluateFieldTag(array $evaluationRule): string
    {
        $payload = $this->probeResult->payload;
        $res = ScraperHelper::readAfter(
            $this->formatIdentifier($evaluationRule),
            $payload['body'],
            false,
            true
        );
        $res = ScraperHelper::readBefore('</'.$evaluationRule['tagType'], $res[0], false, true);

        $toRemove = ScraperHelper::readBefore('>', $res[0]);

        return str_replace($toRemove[0].'>', '', $res[0]);
    }

    private function evaluateFieldText($evaluationRule): string
    {
        $payload = $this->probeResult->payload;
        $res = ScraperHelper::readBetween(
            $evaluationRule['identifier'],
            $evaluationRule['closeIdentifier'],
            $payload['body'],
            false,
            true
        );

        return $res[0];
    }

    private function formatIdentifier(array $evaluationRule)
    {
        if ($evaluationRule['identifier'][count($evaluationRule['identifier'] - 1)] === "*") {
            return $evaluationRule['attribute'].'="'.substr($evaluationRule['identifier'], 0, -1);
        }

        return $evaluationRule['attribute'].'="'.$evaluationRule['identifier'].'"';
    }

}