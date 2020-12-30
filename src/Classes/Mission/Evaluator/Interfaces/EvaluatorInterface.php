<?php

namespace App\Classes\Mission\Evaluator\Interfaces;

interface EvaluatorInterface
{

	public function __construct(array $payload);

	public function evaluate(array $evaluationRule): array;

}