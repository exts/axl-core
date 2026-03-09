<?php
namespace AxlCore\Conversation\Content;

class FileToBase64 extends Content
{
    protected ContentType $type = ContentType::documentToBase64;

    public function data(): string
    {
        if(!file_exists($this->data)) {
            throw new \Exception("The local image file doesn't exist: $this->data");
        }

        $content = file_get_contents($this->data);

        return base64_encode($content);
    }
}