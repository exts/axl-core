<?php
namespace AxlCore;

use AxlCore\Contracts\ContextInterface;
use AxlCore\Contracts\ProviderInterface;
use AxlCore\Contracts\Response\Objects\FileResponseObjectInterface;
use AxlCore\Contracts\Response\Objects\ResponseObjectInterface;
use AxlCore\Contracts\Response\Objects\TextResponseObjectInterface;
use AxlCore\Dispatcher\Dispatcher;
use AxlCore\Exceptions\Request\PayloadException;
use AxlCore\Exceptions\Request\PayloadOptionException;
use AxlCore\Request\PayloadBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class AxlCore
{
    public function __construct(
        protected PayloadBuilder $payload_builder,
        protected ClientInterface $http_client,
        protected ProviderInterface $provider,
        protected StreamFactoryInterface $stream_factory,
        protected RequestFactoryInterface $request_factory,
    ){
    }

    /**
     * @param ContextInterface $context
     * @return \Generator
     * @throws Exceptions\Request\PayloadException
     * @throws Exceptions\Request\PayloadOptionException|ClientExceptionInterface
     */
    public function stream(ContextInterface $context) : \Generator
    {
        $model = $this->provider->getModelOrThrow();

        // generate payload so we can send an http request
        $this->payload_builder->buildPayload($context);

        if(!isset($payload['stream']) || $payload['stream'] === false) {
            throw new PayloadException("You getProvider/model doesn't support streaming. Make sure it's enabled");
        }

        // dispatch payload
        $dispatcher = new Dispatcher(
            $this->provider->getApiUrl(),
            $this->http_client,
            $this->stream_factory,
            $this->request_factory,
        );

        $endpoint = $model->getEndpoint();

        $generator = $dispatcher->dispatchStream(
            $this->provider->endpoint($endpoint),
            $payload,
            $this->provider->getHeaders(),
        );

        $stream_parser = $model->registerStreamParser();
        foreach($generator as $response) {
            $this->provider->handleErrors($response);
            yield $stream_parser($response);
        }
    }

    /**
     * @param ContextInterface $context
     * @return ResponseObjectInterface|TextResponseObjectInterface|FileResponseObjectInterface
     * @throws ClientExceptionInterface
     * @throws PayloadException
     * @throws PayloadOptionException
     */
    public function complete(ContextInterface $context)
        : ResponseObjectInterface | TextResponseObjectInterface | FileResponseObjectInterface
    {
        $model = $this->provider->getModelOrThrow();

        // generate payload so we can send an http request
        $payload = $this->payload_builder->buildPayload($context);

        // dispatch payload
        $dispatcher = new Dispatcher(
            $this->provider->getApiUrl(),
            $this->http_client,
            $this->stream_factory,
            $this->request_factory,
        );

        $endpoint = $model->getEndpoint();
        $response = $dispatcher->dispatch(
            $this->provider->endpoint($endpoint),
            $payload,
            $this->provider->getHeaders(),
        );

        // parse response
        $response_parser = $model->getResponseTypeFormatter($context->getResponseType());
        $response = $response_parser($response->getBody());

        // errors
        $this->provider->handleErrors($response);

        return $response;
    }
}