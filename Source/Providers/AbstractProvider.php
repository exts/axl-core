<?php
namespace AxlCore\Providers;

use AxlCore\Contracts\Models\ModelInterface;
use AxlCore\Contracts\ProviderInterface;
use AxlCore\Contracts\Response\Objects\ResponseObjectInterface;
use AxlCore\Exceptions\Provider\InvalidProviderEndpointException;
use AxlCore\Exceptions\Provider\InvalidProviderModelException;
use AxlCore\Exceptions\Response\ProviderResponseErrorException;
use AxlCore\OptionsResolver;
use AxlCore\Request\HeaderContainer;

abstract class AbstractProvider implements ProviderInterface
{
    public ?string $api_url = null;
    protected ?string $model; // selected model
    protected ?string $api_key;
    protected array $models = [];
    protected array $model_instance = [];
    protected array $endpoints = [];
    protected array $endpoint_options_required = [];
    protected array $endpoint_mapped_options = [];
    protected array $endpoint_accepted_options = [];

    protected array $provider_options = [];

    protected HeaderContainer $header_container;

    public function __construct()
    {
        $this->header_container = new HeaderContainer();
    }

    public function getApiKey() : ?string
    {
        return $this->api_key ?? null;
    }

    public function setApiKey(string $key) : void
    {
        $this->api_key = $key;
    }

    public function getApiUrl() : ?string
    {
        return $this->api_url;
    }

    public function select(string $model, ?string $version = null) : ModelInterface
    {
        $curr = $this->getModelOrThrow($model);
        $this->model = $model;

        if(!empty($version)) {
            $curr->setModelVersion($version);
        }

        return $curr;
    }

    public function getModelOrThrow(?string $model_name = null) : ModelInterface
    {
        $model_name = $model_name ?? $this->model;
        /** @var ?ModelInterface $instance */
        $instance = $this->model_instance[$model_name] ?? null;
        if(!empty($instance)) {
            return $instance;
        }

        $callable = $this->models[$model_name] ?? throw new InvalidProviderModelException("No model was found or set. Make sure the model is registered before calling this method.");

        /** @var ModelInterface $model */
        $model = $callable($this->mappedOptions(), $this->acceptedOptions());
        if(!$model instanceof ModelInterface) {
            throw new InvalidProviderModelException("Model $model_name is not an instance of ModelInterface");
        }

        // initialize if not called
        $model->modelInitialize();

        return $this->model_instance[$model_name] = $model;
    }

    public function register(string $model_name, callable $model) : void
    {
        $this->models[$model_name] = $model;
    }

    public function registerEndpointsOrThrow(array $endpoints) : void
    {
        $tmp = [];
        foreach($endpoints as $endpoint) {
            if(!$endpoint instanceof ProviderEndpoint) {
                throw new InvalidProviderEndpointException("Invalid 'Endpoint' attempted to be registered");
            }

            $tmp[$endpoint->type] = $endpoint->path;
        }

        $this->endpoints = $tmp;
    }

    public function handleErrors(ResponseObjectInterface|array $response_object) : void
    {
        $data = is_array($response_object) ? $response_object : $response_object->rawJsonArray();
        $parsed = $this->registerErrorParser($data);
        if(!empty($parsed)) {
            $type = $parsed['type'] ?? 'unknown error type';
            $message = $parsed['message'] ?? 'unknown error';
            throw new ProviderResponseErrorException(sprintf("[Response error: %s] %s", $type, $message));
        }
    }

    public function endpoint(string $endpoint) : string
    {
        /** @var  */
        return $this->endpoints[$endpoint] ?? throw new InvalidProviderEndpointException("Provider Endpoint doesn't exist");
    }

    public function mappedOptions(?array $options = []) : array
    {
        if(!empty($options)) {
            return $this->endpoint_mapped_options = $options;
        }

        return $this->endpoint_mapped_options ?? [];
    }

    public function acceptedOptions(?array $options = []) : array
    {
        if(!empty($options)) {
            return $this->endpoint_accepted_options = $options;
        }

        return $this->endpoint_accepted_options ?? [];
    }

    public function endpointOptionsRequired(?array $options = []) : array
    {
        if(!empty($options)) {
            return $this->endpoint_options_required = $options;
        }

        return $this->endpoint_options_required ?? [];
    }

    /**
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function setOptions(array $options = []) : void
    {
        if(empty($options)) {
            return;
        }

        $this->provider_options = $options;
    }

    public function getOptions() : array
    {
        return $this->provider_options;
    }

    public function getHeaders() : array
    {
        return $this->getHeadersContainer()->getHeaders();
    }

    public function getHeadersContainer() : HeaderContainer
    {
        return $this->header_container;
    }

    public function setupHeaders(array $headers, array $header_options = []) : void
    {
        $container = $this->getHeadersContainer();
        $container->addHeaders($this->authHeaders());
        $container->addHeaders($headers, $header_options);
    }

    abstract public function initialize() : void;
    abstract public function registerErrorParser(array $response_data) : ?array;
    abstract function authHeaders(?string $key = null) : array;
}