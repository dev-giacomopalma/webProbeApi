<?php

namespace App\Services\WebProbe\Missions\Evaluator;

use App\Services\WebProbe\Missions\Evaluator\Interfaces\EvaluatorInterface;
use Exception;

class HrefEvaluator implements EvaluatorInterface
{

	/** @var array */
	private $payload;

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	public function evaluate(array $evaluationRule): array
	{
		$res = [];
		try {
			$payload = $this->payload;
			$query = "//a[@class='".$evaluationRule['identifier']."']/@href";
			$queryResult = DomxqueryEvaluator::executeDomQuery($payload['body'], $query);
			for($i=0; $i < $queryResult->count(); $i++) {
				$res[$i] = (string) $queryResult->item($i)->nodeValue;
			}
		} catch (Exception $exception) {
			throw new Exception($exception->getMessage());
		}
		return $res;
	}
}