<?php

namespace App\Services\WebProbe\Probes\DiscoveryLibraries\Dtos;

class Price
{
    /** @var array $stack */
    public $stack;

    /** @var string $value */
    public $value;

    public function __construct(array $stack, string $value) {
        $this->stack = $stack;
        $this->value = $value;
    }

    public function getStack(): array
    {
        return $this->stack;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}