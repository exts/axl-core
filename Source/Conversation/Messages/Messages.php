<?php
namespace AxlCore\Conversation\Messages;

class Messages
{
    public static function developer()
    {

    }

    public static function system(...$content) : MessageObject
    {
        return new MessageObject(MessageType::system, $content);
    }

    public static function user(...$content) : MessageObject
    {
        return new MessageObject(MessageType::user, $content);
    }

}