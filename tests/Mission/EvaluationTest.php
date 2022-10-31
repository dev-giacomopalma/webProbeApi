<?php

namespace App\Tests\Mission;

use App\Classes\LaunchPad\ApiLaunchPad;
use App\Classes\Mission\ApiMission;
use App\Classes\Mission\Dto\FieldDto;
use App\Classes\Probe\ApiProbe;
use Exception;
use PHPUnit\Framework\TestCase;
use twittingeek\webProbe\Missions\Settings\MissionSetting;
use twittingeek\webProbe\Probes\Settings\ProbeSetting;

class EvaluationTest extends TestCase
{

	const URL = 'http://www.getyourguide.com';

	/** @dataProvider evaluationProvider
	 * @param array $evaluation
	 * @param $expectedResults
	 */
	public function testEvaluation(array $evaluation) {
		$probeSetting = new ProbeSetting(self::URL);
		$probe = new ApiProbe($probeSetting);
		$missionSetting = new MissionSetting('single', $evaluation);
		$mission = new ApiMission($missionSetting, $probe);
		$launchPad = new ApiLaunchPad($mission);

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
							'tagType' => 'h2',
							'attribute' => 'class',
							'identifier' => 'destinations-slider__title',
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
             * I am not really sure what this test was supposed to to, so I will skip it for now until I find out
             *
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