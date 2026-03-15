<?php
namespace AxlCore\Conversation\Content;

use AxlCore\DataBytePresenter;

class ImageRawToBase64 extends Content
{
    protected ContentType $type = ContentType::imageToBase64;

    public function data(): string
    {
        if(empty($this->data)) {
            throw new \Exception("The data is empty");
        }

        return sprintf("Base64 Image Json Object:\n%s",
            json_encode([
                'file_size' => DataBytePresenter::formatBytes(strlen($this->data)),
                'file_content' => base64_encode($this->data),
                'file_mimetype' => $this->metadata('mimetype'),
            ])
        );
    }
}