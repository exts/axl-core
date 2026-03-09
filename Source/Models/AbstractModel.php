<?php

namespace AxlCore\Models;

use AxlCore\Contracts\Models\ModelInterface;
use AxlCore\Conversation\Content\ContentType;
use AxlCore\Response\ResponseType;
use function in_array;

abstract class AbstractModel implements ModelInterface
{
    public const string MODEL_NAME = 'Abstract Anthropic Model';

    protected string $model_name;
    protected string $model_version;
    protected string $model_type;

    protected ?string $model_endpoint;

    /**
     * @var ResponseType[] $supported_responses
     */
    protected array $supported_responses = []; // TODO: add context check in main code for this
    protected array $content_type_format = [];
    protected array $response_type_formatter = [];

    protected array $role_mapping = [];

    protected ?\Closure $stream_parser;

    protected bool $initialized = false;

    protected string $content_key = 'content';

    protected string $messages_key = 'messages';

    public function __construct(
        protected array $endpoint_mapped_options = [],
        protected array $endpoint_accepted_options = [],
    ){
        if($this->getDefinedModelName() !== null) {
            $this->setModel($this->getDefinedModelName());
        }
    }

    public function setModel(string $model) : void
    {
        $this->setModelVersion($this->model_name = $model);
    }

    public function getModel() : string
    {
        return $this->model_name;
    }

    public function setModelVersion(string $model) : void
    {
        $this->model_version = $model;
    }

    public function getModelVersion() : string
    {
        return $this->model_version;
    }

    public function modelInitialize() : void
    {
        if(!$this->initialized) {
            $this->initialize();
            $this->initialized = true;
        }
    }

    public function getDefinedModelName() : ?string
    {
        return static::MODEL_NAME ?? null;
    }

    public function modelType(?string $model_type = null) : ?string
    {
        if(!empty($model_type)) {
            return $this->model_type = $model_type;
        }
        return $this->model_type;
    }

    public function getEndpoint() : ?string
    {
        return $this->model_endpoint;
    }

    public function setEndpoint(string $endpoint) : void
    {
        $this->model_endpoint = $endpoint;
    }

    public function getMappedOptions() : array
    {
        return $this->endpoint_mapped_options;
    }

    public function setMappedOptions(array $options) : void
    {
        $this->endpoint_mapped_options = $options;
    }

    public function addMappedOptions(?array $options = []) : void
    {
        $this->endpoint_mapped_options = array_merge(
            $this->endpoint_mapped_options, $options);
    }

    public function setAcceptedOptions(array $options) : void
    {
        $this->endpoint_accepted_options = $options;
    }

    public function extractSystemMessages() : bool
    {
        return false;
    }

    public function buildSystemPayload(array $messages) : mixed
    {
        return null;
    }

    public function unsetAcceptedOptions(array $options) : void
    {
        $kept = [];
        foreach($this->endpoint_accepted_options as $accepted_option) {
            if(in_array($accepted_option, $options)) continue;
            $kept[] = $accepted_option;
        }

        $this->endpoint_accepted_options = $kept;
    }

    public function getAcceptedOptions() : array
    {
        return $this->endpoint_accepted_options;
    }

    public function addAcceptedOptions(?array $options = []) : void
    {
        $this->endpoint_accepted_options = array_merge(
            $this->endpoint_accepted_options, $options);
    }

    public function registerResponseTypesOrThrow(array $response_types) : void
    {
        if(empty($response_types)) {
            throw new \Exception("No response types were passed to be registered");
        }

        foreach($response_types as $response_type) {
            if(!$response_type instanceof ResponseType) {
                throw new \Exception("Invalid 'ResponseType' atempted to be registered");
            }

            $this->supported_responses = $response_types;
        }
    }

    /**
     * @param ResponseType $type
     * @param callable $parser
     * @return void
     */
    public function registerResponseTypeObjectFormatter(ResponseType $type, callable $parser) : void
    {
        $this->response_type_formatter[$type->name] = $parser;
    }

    public function registerStreamParser(?\Closure $template = null) : \Closure
    {
        if(!empty($template)) {
            return $this->stream_parser = $template;
        }
        return $this->stream_parser ?? fn() => '';
    }

    /**
     * Register a content type with its block structure
     *
     * @param ContentType $type Content type name (text, image, audio, etc.)
     * @param callable $template Closure that takes the content value and returns array structure
     */
    public function registerContentTypeFormatter(ContentType $type, callable $template) : void
    {
        $this->content_type_format[$type->name] = $template;
    }

    /**
     * @param ResponseType $type
     * @return callable
     * @throws \Exception
     */
    public function getResponseTypeFormatter(ResponseType $type) : callable
    {
        return $this->response_type_formatter[$type->name] ?? throw new \Exception("ResponseParser not registered");
    }

    public function getContentTemplate(ContentType $type) : callable
    {
        return $this->content_type_format[$type->name]
            ?? throw new \Exception("Model template '{$type->name}' doesn't exist");
    }

    public function mapRole(string $role) : string
    {
        return $this->role_mapping[$role] ?? $role;
    }

    /**
     * Register role name mappings for this model
     */
    public function registerRoleMappings(array $mappings) : void
    {
        $this->role_mapping = $mappings;
    }

    public function contentKey(?string $key = null) : ?string
    {
        if(!empty($key)) {
            return $this->content_key = $key;
        }

        return $this->content_key;
    }

    public function messagesKey(?string $key = null) : ?string
    {
        if(!empty($key)) {
            return $this->messages_key = $key;
        }

        return $this->messages_key;
    }

    /**
     * @throws
     * @return void
     */
    public function initialize() : void {}
}