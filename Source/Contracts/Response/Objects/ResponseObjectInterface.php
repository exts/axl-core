<?php
namespace AxlCore\Contracts\Response\Objects;

interface ResponseObjectInterface
{
    public function raw() : string;
    public function rawJson(?string $data = null) : mixed;
    public function rawJsonArray(string $data = null) : ?array;
}