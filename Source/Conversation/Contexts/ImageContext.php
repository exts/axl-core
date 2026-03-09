<?php
namespace AxlCore\Conversation\Contexts;

use AxlCore\Response\ResponseType;

class ImageContext extends Context
{
    protected ResponseType $response_type = ResponseType::Image;
}