<?php
namespace AxlCore;

readonly class EndpointOptions
{
    public function __construct(
        protected array $options = [],
    ){
    }

    public function all() : array
    {
        return $this->options;
    }

    public function fromEndpointOptions(EndpointOptions $options) : EndpointOptions
    {
        return new EndpointOptions(array_merge($this->all(), $options->all()));
    }
}