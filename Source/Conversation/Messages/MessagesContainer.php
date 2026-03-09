<?php
namespace AxlCore\Conversation\Messages;

use AxlCore\Contracts\ContentInterface;
use function array_filter;

class MessagesContainer
{
    public function __construct(
        protected array $messages,
    ){
        array_filter($this->messages, function($data) {
           if(!$data instanceof ContentInterface) {
               throw new \Exception("message isn't a new context type");
           }
        });
    }


}