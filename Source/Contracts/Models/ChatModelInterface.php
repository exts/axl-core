<?php
namespace AxlCore\Contracts\Models;

use AxlCore\Conversation\Content\ContentType;

interface ChatModelInterface extends ModelInterface
{
    public function registerContentFlattenedItem(ContentType $type, \Closure $closure) : void;
    public function registerContentFlattenedItems(array $type_key) : void;
    public function isFlattenPromptEnabled() : bool;
    public function setFlattenPrompt(bool $bool) : void;
    public function enableFlattenPrompt() : void;
    public function disableFlattenPrompt() : void;
    public function getContentTemplateFlattened(ContentType $type) : ?callable;
}