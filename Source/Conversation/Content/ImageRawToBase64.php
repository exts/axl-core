<?php
namespace AxlCore\Conversation\Content;

use AxlCore\DataBytePresenter;
use function base64_decode;
use function base64_encode;

class ImageRawToBase64 extends Content
{
    protected ContentType $type = ContentType::imageToBase64;

    public function data() : string
    {
        if(empty($this->data)) {
            throw new \Exception("The data is empty");
        }

        $is_64 = base64_decode(($this->data, true);
        if($is_64 !== false) {
            return $this->data;
        }

        return base64_encode($this->data);
    }
}