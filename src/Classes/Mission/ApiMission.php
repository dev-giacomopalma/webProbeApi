<?php

namespace App\Classes\Mission;

use App\Classes\Exceptions\FieldEvaluationException;
use App\Classes\Exceptions\UnsupportedEvaluationRuleTypeException;
use App\Classes\Exceptions\UnsupportedResultTypeException;
use App\Classes\Mission\Dto\FieldDto;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
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
    private const EVALUATION_RULE_TYPE_HREF_NAME = 'href';
    private const EVALUATION_RULE_TYPE_DOM_QUERY_NAME = 'domxquery';

    private const ALLOWED_EVALUATION_RULE_TYPES = [
        self::EVALUATION_RULE_TYPE_TAG_NAME,
        self::EVALUATION_RULE_TYPE_TEXT_NAME,
        self::EVALUATION_RULE_TYPE_HREF_NAME,
        self::EVALUATION_RULE_TYPE_DOM_QUERY_NAME,
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
                        case self::EVALUATION_RULE_TYPE_HREF_NAME:
                            try {
                                $resEvaluation[$name] = $this->evaluateFieldHref($evaluationRule);
                            } catch (Exception $e) {
                                throw new FieldEvaluationException(
                                    sprintf(
                                        'Impossible to evaluate field: %s, verify the request is correct %s'.$e->getMessage(),
                                        $name,
                                        json_encode($evaluationRule)
                                    )
                                );
                            }
                            break;
                        case self::EVALUATION_RULE_TYPE_DOM_QUERY_NAME:
                            try {
                                $resEvaluation[$name] = $this->evaluateDomQuery($evaluationRule);
                            } catch (Exception $e) {
                                throw new FieldEvaluationException(
                                    sprintf(
                                        'Impossible to evaluate field: %s, verify the request is correct %s'.$e->getMessage(),
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
                    $field->value = $items[$i] ?? '';

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
        $res = [];
        try {
            $payload = $this->probeResult->payload;
            if ($evaluationRule['identifier'][strlen($evaluationRule['identifier']) - 1] === "*") {
                $evaluationRule['identifier'] = substr(
                    $evaluationRule['identifier'],
                    0,
                    -1
                );
                $query = "//{$evaluationRule['tagType']}[starts-with(@{$evaluationRule['attribute']},'".$evaluationRule['identifier']."')]";
            } else {
                $query = "//{$evaluationRule['tagType']}[@{$evaluationRule['attribute']}='".$evaluationRule['identifier']."']";
            }
            $queryResult = $this->executeDomQuery($payload['body'], $query);
            for($i=0; $i < $queryResult->count(); $i++) {
                $res[$i] = (string) utf8_decode($queryResult->item($i)->nodeValue);
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
        return $res;
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

    private function evaluateFieldHref($evaluationRule)
    {
        $res = [];
        try {
            $payload = $this->probeResult->payload;
            $query = "//a[@class='".$evaluationRule['identifier']."']/@href";
            $queryResult = $this->executeDomQuery($payload['body'], $query);
            for($i=0; $i < $queryResult->count(); $i++) {
                $res[$i] = (string) $queryResult->item($i)->nodeValue;
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
        return $res;
    }

    private function evaluateDomQuery($evaluationRule)
    {
        $res = [];
        try {
            $payload = $this->probeResult->payload;
            $queryResult = $this->executeDomQuery($payload['body'], $evaluationRule['query']);
            if (isset($evaluationRule['node'])) {
                if ($evaluationRule['node'] < $queryResult->count()) {
                    $res[0] = (string) utf8_decode(($queryResult->item((int) $evaluationRule['node'])->nodeValue));
                } else {
                    $res[0] = '';
                }
            } else {
                for ($i = 0; $i < $queryResult->count(); $i++) {
                    $res[$i] = (string) utf8_decode($queryResult->item($i)->nodeValue);
                }
            }
        } catch (Exception $exception) {
            if (isset($evaluationRule['optional']) && $evaluationRule['optional'] !== true) {
                throw new Exception($exception->getMessage());
            }
        }
        return $res;
    }

    private function executeDomQuery(string $body, string $query): DOMNodeList
    {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->loadHTML($body);
        $xpath = new DOMXPath($doc);
        return $xpath->query($query);
    }

}
