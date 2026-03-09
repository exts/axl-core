<?php
namespace AxlCore\Contracts\Response\Objects;

interface TextResponseObjectInterface extends ResponseObjectInterface
{
    public function text() : ?string;
    public function json(?string $data = null) : mixed;
    public function jsonArray(string $data = null) : ?array;
}