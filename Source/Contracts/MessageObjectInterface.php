<?php
namespace AxlCore\Contracts;

use AxlCore\Conversation\Messages\MessageType;

interface MessageObjectInterface
{
    public function content() : array;
    public function type() : MessageType;
}