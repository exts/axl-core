<?php
namespace AxlCore\Response\Objects;

use AxlCore\Contracts\Response\Objects\TextResponseObjectInterface;

abstract class TextResponseObject extends AbstractResponseObject implements TextResponseObjectInterface
{
    abstract public function text() : ?string;

    public function json(?string $data = null) : mixed
    {
        return json_decode($data ?? $this->text());
    }

    public function jsonArray(string $data = null) : ?array
    {
        return json_decode($data ?? $this->text(), true);
    }
}