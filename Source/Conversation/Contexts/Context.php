<?php
namespace AxlCore\Conversation\Contexts;

use AxlCore\Contracts\ContextInterface;
use AxlCore\Contracts\MessageObjectInterface;
use AxlCore\Response\ResponseType;

class Context implements ContextInterface
{
    protected ResponseType $response_type;

    protected array $message_objects = [];

    public function __construct(
        ...$message_objects,
    ){
        foreach($message_objects as $current) {
            if(!$current instanceof MessageObjectInterface) {
                throw new \Exception("Invalid message type passed, must be of type: ContentInterface");
            }

            $this->message_objects[$current->type()->name] = $current;
        }
    }

    public function getMessages(?string $type = null) : array|MessageObjectInterface
    {
        return empty($type) ? $this->message_objects : $this->message_objects[$type] ?? [];
    }

    public function getResponseType() : ResponseType
    {
        if(!isset($this->response_type)) {
            throw new \Exception("No Response Type was set for this context");
        }
        return $this->response_type;
    }

}




