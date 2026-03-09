<?php
namespace AxlCore\Contracts\Response\Objects;

interface FileResponseObjectInterface extends ResponseObjectInterface
{
    public function url() : string;
    public function base64() : string;
    public function metadata() : array;
}