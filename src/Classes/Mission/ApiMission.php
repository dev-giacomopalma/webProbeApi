<?php


namespace App\Classes\Mission;


use App\Classes\Mission\Dto\FieldDto;
use twittingeek\webProbe\Missions\BaseMission;
use twittingeek\webProbe\Missions\Interfaces\MissionResult;
use twittingeek\webProbe\Probes\Helpers\ScraperHelper;
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
            $firstKey = null;
            /** @var array $evaluation */
            foreach ($this->missionSetting->getEvaluation() as $evaluationRules) {
                $resEvaluation = [];
                foreach ($evaluationRules as $name => $evaluationRule) {
                    if (null === $firstKey) {
                        $firstKey = $name;
                    }
                    switch ($evaluationRule['type']) {
                        case "tag":
                            $resEvaluation[$name] = $this->evaluateFieldTag($evaluationRule);
                            break;
                        case "text":
                            $resEvaluation[$name] = $this->evaluateFieldText($evaluationRule);
                            break;
                        default:
                            //todo throw unsupported type exception
                            break;
                    }
                }
            }

            if (empty($resEvaluation)) {
                return [];
            }
            if ($this->missionSetting->getResultType() === 'all') {
                $resCount = count($resEvaluation[$name]);
            } elseif ($this->missionSetting->getResultType() === 'single') {
                $resCount = 1;
            }
            $return = [];
            for ($i=0; $i<$resCount; $i++) {
                $res = [];
                foreach ($resEvaluation as $key => $items) {
                    $field = new FieldDto();
                    $field->name = $key;
                    $field->value = $items[$i];

                    $res[] = $field;
                }
                $return[] = $res;
            }

            return new MissionResult(MissionResult::OK_STATUS_CODE, $return);

        }

        return new MissionResult(MissionResult::OK_STATUS_CODE, $this->probeResult->payload);

    }

    private function evaluateFieldTag(array $evaluationRule): array
    {
        $return = [];
        $payload = $this->probeResult->payload;
        $results = ScraperHelper::readAfter(
            $this->formatIdentifier($evaluationRule),
            $payload['body'],
            true,
            true
        );

        foreach ($results as $key => $res) {
            $internalResults = ScraperHelper::readBefore('</'.$evaluationRule['tagType'], $res, false, true);
            $toRemove = ScraperHelper::readBefore('>', $internalResults[0]);

            $return[] = str_replace($toRemove[0].'>', '', $internalResults[0]);
        }

        return $return;
    }

    private function evaluateFieldText($evaluationRule): array
    {
        $payload = $this->probeResult->payload;
        $res = ScraperHelper::readBetween(
            $evaluationRule['identifier'],
            $evaluationRule['closeIdentifier'],
            $payload['body'],
            true,
            true
        );

        return $res;
    }

    private function formatIdentifier(array $evaluationRule)
    {
        if ($evaluationRule['identifier'][strlen($evaluationRule['identifier']) - 1] === "*") {
            return $evaluationRule['attribute'].'="'.substr($evaluationRule['identifier'], 0, -1);
        }

        return $evaluationRule['attribute'].'="'.$evaluationRule['identifier'].'"';
    }

}