<?php

namespace AxlCore\Contracts;

use AxlCore\Response\ResponseType;

interface ContextInterface
{
    public function getMessages(?string $type = null) : array|MessageObjectInterface;
    public function getResponseType() : ResponseType;
}