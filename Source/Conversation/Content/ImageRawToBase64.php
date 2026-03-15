<?php
namespace AxlCore\Conversation\Content;

use AxlCore\DataBytePresenter;

class ImageRawToBase64 extends Content
{
    protected ContentType $type = ContentType::imageToBase64;

    public function data() : string
    {
        if(empty($this->data)) {
            throw new \Exception("The data is empty");
        }

        return sprintf('%s;base64,%s', $this->metadata('mimetype'), base64_encode($this->data));
    }
}