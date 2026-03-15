<?php
namespace AxlCore\Conversation\Content;

use AxlCore\DataBytePresenter;

class TextFile extends Content
{
    protected ContentType $type = ContentType::textFile;

    #[\Override]
    public function data() : string
    {
        if(!file_exists($this->data)) {
            throw new \Exception("The TextFile: $this->data doesn't exist");
        }

        $content = file_get_contents($this->data);

        return sprintf("%sAttached Text File Json Object:\n%s", '',
            json_encode([
                'file_name' => $this->metadata('filename'),
                'file_size' => DataBytePresenter::formatBytes(strlen($content)),
                'file_content' => $content,
                'file_mimetype' => $this->metadata('mimetype'),
            ])
        );
    }
}