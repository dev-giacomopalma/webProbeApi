<?php

namespace App\Classes\Mission;

use App\Classes\Exceptions\FieldEvaluationException;
use App\Classes\Exceptions\UnsupportedEvaluationRuleTypeException;
use App\Classes\Exceptions\UnsupportedResultTypeException;
use App\Classes\Mission\Dto\FieldDto;
use Exception;
use twittingeek\webProbe\Missions\BaseMission;
use twittingeek\webProbe\Missions\Interfaces\MissionResult;
use twittingeek\webProbe\Probes\Helpers\ScraperHelper;
use twittingeek\webProbe\Probes\Libraries\DiscoveryLibrary;
use twittingeek\webProbe\Probes\ProbeResult;

class ApiMission extends BaseMission
{

    private const EVALUATION_RULE_TYPE_TAG_NAME = 'tag';
    private const EVALUATION_RULE_TYPE_TEXT_NAME = 'text';

    private const ALLOWED_EVALUATION_RULE_TYPES = [
        self::EVALUATION_RULE_TYPE_TAG_NAME,
        self::EVALUATION_RULE_TYPE_TEXT_NAME
    ];
    private const STATUS_EMPTY = 400;

    private const RESULT_TYPE_ALL_NAME = 'all';
    private const RESULT_TYPE_SINGLE_NAME = 'single';

    private const ALLOWED_RESULT_TYPES = [
        self::RESULT_TYPE_ALL_NAME,
        self::RESULT_TYPE_SINGLE_NAME
    ];

    /** @var ProbeResult */
    private $probeResult;

    /** @var DiscoveryLibrary */
    private $discoveryLibrary;

    /**
     * @return MissionResult
     * @throws Exception
     */
    public function execute(): MissionResult
    {
        $this->probeResult = $this->probe->run();

        if (null !== $this->missionSetting->getEvaluation() && !empty($this->missionSetting->getEvaluation())) {
            $this->discoveryLibrary = new DiscoveryLibrary();
            $firstKey = null;
            /** @var array $evaluation */
            foreach ($this->missionSetting->getEvaluation() as $evaluationRules) {
                $resEvaluation = [];
                foreach ($evaluationRules as $name => $evaluationRule) {
                    if (null === $firstKey) {
                        $firstKey = $name;
                    }
                    switch ($evaluationRule['type']) {
                        case self::EVALUATION_RULE_TYPE_TAG_NAME:
                            try {
                                $resEvaluation[$name] = $this->evaluateFieldTag($evaluationRule);
                            } catch (Exception $e) {
                                throw new FieldEvaluationException(
                                    sprintf(
                                        'Impossible to evaluate field: %s, verify the request is correct %s',
                                        $name,
                                        json_encode($evaluationRule)
                                    )
                                );
                            }
                            break;
                        case self::EVALUATION_RULE_TYPE_TEXT_NAME:
                            try {
                                $resEvaluation[$name] = $this->evaluateFieldText($evaluationRule);
                            } catch (Exception $e) {
                                throw new FieldEvaluationException(
                                    sprintf(
                                        'Impossible to evaluate field: %s, verify the request is correct %s',
                                        $name,
                                        json_encode($evaluationRule)
                                    )
                                );
                            }
                            break;
                        default:
                            throw new UnsupportedEvaluationRuleTypeException(
                                sprintf(
                                    'Unsupported evaluation rule type %s Valid evaluation rule types are: %s',
                                    $evaluationRule['type'],
                                    implode(', ', self::ALLOWED_EVALUATION_RULE_TYPES)
                                    )
                            );
                            break;
                    }
                }
            }

            if (empty($resEvaluation)) {
                return new MissionResult(self::STATUS_EMPTY, []);
            }
            if ($this->missionSetting->getResultType() === self::RESULT_TYPE_ALL_NAME) {
                $resCount = count($resEvaluation[$name]);
            } elseif ($this->missionSetting->getResultType() === self::RESULT_TYPE_SINGLE_NAME) {
                $resCount = 1;
            } else {
                throw new UnsupportedResultTypeException(
                    sprintf(
                        'Unsupported result type %s Valid result types are: %s',
                        $this->missionSetting->getResultType(),
                        implode(', ', self::ALLOWED_RESULT_TYPES)
                    )
                );
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
