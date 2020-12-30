<?php

namespace App\Classes\Mission\Evaluator;

use App\Classes\Mission\Evaluator\Interfaces\EvaluatorInterface;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Exception;

class DomxqueryEvaluator implements EvaluatorInterface
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
			$queryResult = self::executeDomQuery($payload['body'], $evaluationRule['query']);
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

	public static function executeDomQuery(string $body, string $query): DOMNodeList
	{
		libxml_use_internal_errors(true);
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		$doc->loadHTML($body);
		$xpath = new DOMXPath($doc);
		return $xpath->query($query);
	}
}