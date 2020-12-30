<?php

namespace App\Classes\Mission\Evaluator;

use App\Classes\Mission\Evaluator\Interfaces\EvaluatorInterface;
use Exception;

class TagEvaluator implements EvaluatorInterface
{

	/** @var string */
	private $payload;

	public function __construct(string $payload)
	{
		$this->payload = $payload;
	}

	public function evaluate(array $evaluationRule): array
	{
		$res = [];
		try {
			$payload = $this->payload;
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
			$queryResult = DomxqueryEvaluator::executeDomQuery($payload['body'], $query);
			for($i=0; $i < $queryResult->count(); $i++) {
				$res[$i] = (string) utf8_decode($queryResult->item($i)->nodeValue);
			}
		} catch (Exception $exception) {
			throw new Exception($exception->getMessage());
		}
		return $res;
	}
}