<?php
namespace AxlCore\Conversation\Contexts;

use AxlCore\Response\ResponseType;

class TextContext extends Context
{
    protected ResponseType $response_type = ResponseType::Text;
}