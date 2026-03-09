<?php
namespace AxlCore\Conversation\Messages;

enum MessageType
{
    case system;
    case user;
    case assistant;
}