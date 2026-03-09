<?php
namespace AxlCore\Contracts\Models;

use AxlCore\Conversation\Content\ContentType;
use AxlCore\Response\ResponseType;

interface ModelInterface
{
    public function setModel(string $model) : void;
    public function getModel() : string;
    public function setModelVersion(string $model) : void;
    public function getModelVersion() : string;
    public function modelType(?string $model_type = null) : ?string;
    public function getEndpoint() : ?string;
    public function setEndpoint(string $endpoint) : void;
    public function getMappedOptions() : array;
    public function setMappedOptions(array $options) : void;
    public function addMappedOptions(?array $options = []) : void;
    public function setAcceptedOptions(array $options) : void;
    public function unsetAcceptedOptions(array $options) : void;
    public function getAcceptedOptions() : array;
    public function addAcceptedOptions(?array $options = []) : void;
    public function extractSystemMessages() : bool;
    public function buildSystemPayload(array $messages) : mixed;
    public function registerResponseTypesOrThrow(array $response_types) : void;
    public function registerContentTypeFormatter(ContentType $type, callable $template) : void;

    public function registerStreamParser(?\Closure $template = null) : \Closure;

    public function getContentTemplate(ContentType $type) : callable;

    public function mapRole(string $role) : string;
    public function registerRoleMappings(array $mappings) : void;

    public function registerResponseTypeObjectFormatter(ResponseType $type, callable $parser) : void;
    public function getResponseTypeFormatter(ResponseType $type) : callable;
    public function contentKey(?string $key = null) : ?string;
    public function messagesKey(?string $key = null) : ?string;
    public function initialize() : void;
}