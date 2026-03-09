<?php
namespace AxlCore\Conversation\Messages;

use AxlCore\Contracts\ContentInterface;
use AxlCore\Contracts\MessageObjectInterface;

readonly class MessageObject implements MessageObjectInterface
{
    public function __construct(
        protected MessageType $type,
        protected array $content = [],
    ){
        foreach($this->content as $current) {
            if(!$current instanceof ContentInterface) {
                throw new \Exception("Invalid message type passed, must be of type: ContentInterface");
            }
        }
    }

    public function content() : array
    {
        return $this->content;
    }

    public function type() : MessageType
    {
        return $this->type;
    }
}