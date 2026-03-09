<?php
namespace AxlCore\Request;

interface StreamInterface
{
    public function stream() : \Generator;
}