<?php

namespace AxlCore\Contracts;

use AxlCore\Conversation\Content\ContentType;

interface ContentInterface
{
    public function type() : ContentType;
    public function data() : string;
    public function metadata(string $key, ?string $value = null, mixed $default = null) : ?string;
}