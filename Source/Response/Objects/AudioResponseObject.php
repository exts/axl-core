<?php
namespace AxlCore\Response\Objects;

use AxlCore\Contracts\Response\Objects\ImageResponseObjectInterface;

abstract class AudioResponseObject extends AbstractResponseObject implements ImageResponseObjectInterface
{
    abstract public function url() : string;
    abstract public function base64() : string;
}