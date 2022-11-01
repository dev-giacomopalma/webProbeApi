<?php
namespace App\Services\WebProbe\Missions\Settings;

class MissionSetting
{

    /** @var string */
    private $resultType;

    /** @var array */
    private $evaluation;

    public function __construct(string $resultType, array $evaluation = [])
    {
        $this->resultType = $resultType;
        $this->evaluation = $evaluation;
    }

    /**
     * @return string
     */
    public function getResultType(): string
    {
        return $this->resultType;
    }

    /**
     * @return array
     */
    public function getEvaluation(): array
    {
        return $this->evaluation;
    }


}