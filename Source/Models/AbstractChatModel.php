<?php
namespace AxlCore\Models;

use AxlCore\Contracts\Models\ChatModelInterface;
use AxlCore\Conversation\Content\ContentType;

abstract class AbstractChatModel extends AbstractModel implements ChatModelInterface
{
    protected array $content_templates_flattened = [];

    protected bool $flatten_prompt = false;

    /**
     * @param array $type_key
     * @return void
     * @throws \Exception
     */
    public function registerContentFlattenedItems(array $type_key) : void
    {
        foreach($type_key as $current => $value) {
            if(!$current instanceof ContentType || !$value instanceof \Closure) {
                throw new \Exception("Key in the key/value pair must be of ContentType => \Closure");
            }
            $this->content_templates_flattened[$current->name] = $value;
        }
    }

    public function registerContentFlattenedItem(ContentType $type, \Closure $closure) : void
    {
        $this->content_templates_flattened[$type->name] = $closure;
    }

    public function getContentTemplateFlattened(ContentType $type) : ?callable
    {
        return $this->content_templates_flattened[$type->name] ?? null;
    }

    public function enableFlattenPrompt() : void
    {
        $this->flatten_prompt = true;
    }

    public function disableFlattenPrompt() : void
    {
        $this->flatten_prompt = false;
    }

    public function setFlattenPrompt(bool $bool) : void
    {
        $this->flatten_prompt = $bool;
    }

    public function isFlattenPromptEnabled() : bool
    {
        return $this->flatten_prompt;
    }
}