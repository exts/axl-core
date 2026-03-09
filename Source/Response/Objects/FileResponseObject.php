<?php
namespace AxlCore\Response\Objects;

use AxlCore\Contracts\Response\Objects\FileResponseObjectInterface;

abstract class FileResponseObject extends AbstractResponseObject implements FileResponseObjectInterface
{
    abstract public function url() : string;
    abstract public function base64() : string;
    public function metadata() : array
    {
        return [];
    }
}