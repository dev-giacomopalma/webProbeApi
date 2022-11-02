<?php

namespace App\Services\WebProbe\Missions;

use App\Classes\Exceptions\FieldEvaluationException;
use App\Classes\Exceptions\UnsupportedEvaluationRuleTypeException;
use App\Classes\Exceptions\UnsupportedResultTypeException;
use App\Classes\Mission\Dto\FieldDto;
use App\Classes\Mission\Evaluator\Interfaces\EvaluatorInterface;
use App\Services\WebProbe\Missions\Settings\MissionSetting;
use App\Services\WebProbe\Probes\Probe;
use Exception;
use App\Services\WebProbe\Probes\ProbeResult;
use App\Classes\Mission\Evaluator\TagEvaluator;
use App\Classes\Mission\Evaluator\TextEvaluator;
use App\Classes\Mission\Evaluator\HrefEvaluator;
use App\Classes\Mission\Evaluator\DomxqueryEvaluator;

class Mission
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

    /** @var string */
    private $resultType;

    /** @var array */
    private $evaluation;

    /** @var Probe */
    public $probe;

    public function __construct(Probe $probe, string $resultType, array $evaluation = [])
    {
        $this->resultType = $resultType;
        $this->evaluation = $evaluation;
        $this->probe = $probe;
    }

    /**
     * @return MissionResult
     * @throws Exception
     */
    public function execute(): MissionResult
    {
        $this->probeResult = $this->probe->run();

        if (!empty($this->evaluation)) {
            $firstKey = null;
            /** @var array $evaluation */
            foreach ($this->evaluation as $evaluationRules) {
                $resEvaluation = [];
                foreach ($evaluationRules as $name => $evaluationRule) {
                    if (null === $firstKey) {
                        $firstKey = $name;
                    }

                    if (!in_array($evaluationRule['type'], self::ALLOWED_EVALUATION_RULE_TYPES, true)) {
						throw new UnsupportedEvaluationRuleTypeException(
							sprintf(
								'Unsupported evaluation rule type %s Valid evaluation rule types are: %s',
								$evaluationRule['type'],
								implode(', ', self::ALLOWED_EVALUATION_RULE_TYPES)
							)
						);
					}

                    switch ($evaluationRule['type']) {
						case "tag":
							/** @var EvaluatorInterface $evaluator */
							$evaluator = new TagEvaluator($this->probeResult->payload);
							break;
						case "text":
							/** @var EvaluatorInterface $evaluator */
							$evaluator = new TextEvaluator($this->probeResult->payload);
							break;
						case "href":
							/** @var EvaluatorInterface $evaluator */
							$evaluator = new HrefEvaluator($this->probeResult->payload);
							break;
						case "domxquery":
							/** @var EvaluatorInterface $evaluator */
							$evaluator = new DomxqueryEvaluator($this->probeResult->payload);
							break;
					}

					try {
						$resEvaluation[$name] = $evaluator->evaluate($evaluationRule);
					} catch (Exception $e) {
						throw new FieldEvaluationException(
							sprintf(
								'Impossible to evaluate field: %s, verify the request is correct %s',
								$name,
								json_encode($evaluationRule)
							)
						);
					}
                }
            }

            if (empty($resEvaluation)) {
                return new MissionResult(self::STATUS_EMPTY, []);
            }
            if ($this->resultType === self::RESULT_TYPE_ALL_NAME) {
                $resCount = count($resEvaluation[$name]);
            } elseif ($this->resultType === self::RESULT_TYPE_SINGLE_NAME) {
                $resCount = 1;
            } else {
                throw new UnsupportedResultTypeException(
                    sprintf(
                        'Unsupported result type %s Valid result types are: %s',
                        $this->resultType,
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
                    $field->value = trim($items[$i]) ?? '';

                    $res[] = $field;
                }
                $return[] = $res;
            }

            return new MissionResult(MissionResult::OK_STATUS_CODE, $return);

        }

        return new MissionResult(MissionResult::OK_STATUS_CODE, $this->probeResult->payload);

    }

    public function getProbe(): Probe
    {
        return $this->probe;
    }
}
