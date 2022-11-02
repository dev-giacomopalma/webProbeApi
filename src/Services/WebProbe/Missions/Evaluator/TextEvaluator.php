<?php

namespace App\Classes\Mission\Evaluator;

use App\Classes\Mission\Evaluator\Interfaces\EvaluatorInterface;
use App\Services\WebProbe\Probes\Helpers\ScraperHelper;

class TextEvaluator implements EvaluatorInterface
{

	/** @var array */
	private $payload;

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	public function evaluate(array $evaluationRule): array
	{
		$payload = $this->payload;
		$res = ScraperHelper::readBetween(
			$evaluationRule['identifier'],
			$evaluationRule['closeIdentifier'],
			$payload['body'],
			true,
			true
		);

		return $res;
	}
}