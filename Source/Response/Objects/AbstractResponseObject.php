<?php
namespace AxlCore\Response\Objects;

use AxlCore\Contracts\Response\Objects\ResponseObjectInterface;

abstract class AbstractResponseObject implements ResponseObjectInterface
{
    public function __construct(
        protected string $raw_data,
    ){
    }

    public function raw() : string
    {
        return $this->raw_data;
    }

    public function rawJson(?string $data = null) : mixed
    {
        return json_decode($data ?? $this->raw());
    }

    public function rawJsonArray(string $data = null) : ?array
    {
        return json_decode($data ?? $this->raw(), true);
    }
}