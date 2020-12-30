<?php

namespace App\Classes\Mission\Evaluator\Interfaces;

interface EvaluatorInterface
{

	public function __construct(string $payload);

	public function evaluate(array $evaluationRule): array;

}