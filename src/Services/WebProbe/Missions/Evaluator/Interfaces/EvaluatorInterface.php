<?php

namespace App\Services\WebProbe\Missions\Evaluator\Interfaces;

interface EvaluatorInterface
{

	public function __construct(array $payload);

	public function evaluate(array $evaluationRule): array;

}