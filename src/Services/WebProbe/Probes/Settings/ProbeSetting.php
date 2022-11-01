<?php

namespace App\Services\WebProbe\Probes\Settings;

class ProbeSetting
{

    /** @var string */
    private $url;

    /** @var array */
    private $preparation;

    public function __construct(string $url, array $preparation = [])
    {
        $this->url = $url;
        $this->preparation = $preparation;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getPreparation(): array
    {
        return $this->preparation;
    }


}