<?php
namespace AxlCore\Contracts;

use AxlCore\Contracts\Models\ModelInterface;
use AxlCore\Contracts\Response\Objects\ResponseObjectInterface;
use AxlCore\Request\HeaderContainer;

interface ProviderInterface
{
    public function getApiKey() : ?string;
    public function setApiKey(string $key) : void;
    public function getApiUrl() : ?string;
    public function select(string $model) : ModelInterface;
    public function getModelOrThrow(?string $model_name = null) : ModelInterface;
    public function register(string $model_name, callable $model) : void;
    public function registerEndpointsOrThrow(array $endpoints) : void;
    public function handleErrors(ResponseObjectInterface|array $response_object) : void;
    public function endpoint(string $endpoint) : string;
    public function mappedOptions(?array $options = []) : array;
    public function acceptedOptions(?array $options = []) : array;
    public function endpointOptionsRequired(?array $options = []) : array;
    public function setOptions(array $options) : void;
    public function getOptions() : array;
    public function getHeaders() : array;
    public function getHeadersContainer() : HeaderContainer;
    public function setupHeaders(array $headers, array $header_options = []) : void;
    public function initialize() : void;
    public function authHeaders(?string $key = null) : array;
    public function registerErrorParser(array $response_data) : ?array;
}