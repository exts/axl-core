<?php
namespace AxlCore\Conversation\Content;

use AxlCore\Contracts\ContentInterface;

class Content implements ContentInterface
{
    protected ContentType $type;

    public function __construct(
        protected string $data,
        protected array $meta_data = [],
    ){
    }

    /**
     * TODO: Decide on if i should keep this or not
     */
    public static function make(string $context, string $value) : ContentInterface
    {
        return match($context) {
            'text', Text::class => new Text($value),
            'imgId', 'imageId', 'imageFileId', ImageFileId::class => new ImageFileId($value),
            'imgBase64', 'imageBase64', ImageFileToBase64::class => new ImageFileToBase64($value),
            'mp3', 'audio', Audio::class => new Audio($value),
            default => throw new \Exception("Invalid Context")
        };
    }

    public function type() : ContentType
    {
        return $this->type;
    }

    public function data() : string
    {
        return $this->data;
    }

    public function metadata(string $key, ?string $value = null, mixed $default = null) : ?string
    {
        if(!isset($value)) {
            return $this->meta_data[$key] ?? $default;
        }
        return $this->meta_data[$key] = $value;
    }


}