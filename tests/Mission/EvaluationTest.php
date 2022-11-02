<?php

namespace App\Tests\Mission;

use App\Services\WebProbe\LaunchPad\LaunchPad;
use App\Services\WebProbe\Missions\Dto\FieldDto;
use App\Services\WebProbe\Missions\Mission;
use App\Services\WebProbe\Probes\Probe;
use Exception;
use PHPUnit\Framework\TestCase;

class EvaluationTest extends TestCase
{

	const URL = 'http://www.getyourguide.com';

	/** @dataProvider evaluationProvider
	 * @param array $evaluation
	 * @param $expectedResults
	 */
	public function testEvaluation(array $evaluation) {
		$probe = new Probe(self::URL);
		$mission = new Mission($probe,'single', $evaluation);
		$launchPad = new LaunchPad($mission);

		try {
			$missionResult = $launchPad->launch();
		} catch (Exception $exception) {
			print($exception->getMessage());
		}

		foreach ($missionResult->getPayload() as $payload) {

			/** @var FieldDto $field */
			foreach ($payload as $field) {
				$this->assertNotEmpty($field->value);
			}
		}
	}

	public function evaluationProvider(): array {
		return [
			'test evaluation tag' => [
				'evaluation' => [
					[
						'id' => [
							'type' => 'tag',
							'tagType' => 'h1',
							'attribute' => 'class',
							'identifier' => 'hero-section__title',
						],
					],
				]
			],
			'test evaluation href' => [
				'evaluation' => [
					[
						'id' => [
							'type' => 'href',
							'identifier' => 'skip-link',
						],
					],
				]
			],
            /**
			'test evaluation text' => [
				'evaluation' => [
					[
						'id' => [
							'type' => 'text',
							'identifier' => 'intro-banner-title',
							'closeIdentifier' => '/div',
						],
					],
				]
			],
             */
			'test evaluation domxquery' => [
				'evaluation' => [
					[
						'id' => [
							'type' => 'domxquery',
							'query' => "//span[@class='item-title']",
							'node' => 1
						],
					],
				]
			],
		];
	}
}