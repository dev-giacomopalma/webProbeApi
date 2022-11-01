<?php

namespace App\Services\WebProbe\Missions\Interfaces;

class MissionResult
{

    public const OK_STATUS_CODE = 200;

    /** @var int */
    private $statusCode;

    /** @var array */
    private $payload;

    public function __construct(int $statusCode, array $payload)
    {
        $this->statusCode = $statusCode;
        $this->payload = $payload;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}