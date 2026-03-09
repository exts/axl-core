<?php
namespace AxlCore;

use AxlCore\Contracts\ProviderInterface;
use AxlCore\Exceptions\Provider\InvalidProviderRegistryException;
use AxlCore\Models\ModelType;
use AxlCore\Providers\Anthropic\AnthropicProvider;
use AxlCore\Providers\Google\GeminiProvider;
use AxlCore\Providers\Llstudio\LlstudioProvider;
use AxlCore\Providers\OpenAi\OpenAiProvider;
use AxlCore\Providers\ProviderRegistry;
use AxlCore\Request\HeaderContainer;
use AxlCore\Request\PayloadBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Axl
{
    protected ProviderInterface $provider;

    protected static Axl $instance;

    protected array $headers = [];
    protected array $header_options = [];

    public function __construct(
        protected ClientInterface $http_client = new Client(['stream' => true]),
        protected ?ProviderRegistry $provider_registry = null,
        protected ?StreamFactoryInterface $stream_factory = null,
        protected ?RequestFactoryInterface $request_factory = null,
    ){
        if((!isset($this->stream_factory) && isset($this->request_factory))
            || (!isset($this->request_factory) && isset($this->stream_factory))) {
                throw new \Exception("You must provide both a PSR stream factory interface and request factory interface");
            }

        //TODO: do we need one or two instances, keep this the same otherwise
        // setup defaults
        if(!isset($this->stream_factory, $this->request_factory)) {
            $http_factory = new HttpFactory();
            $this->stream_factory = $http_factory;
            $this->request_factory = $http_factory;
        }

        //setup default provider registry from singleton, supports SOLID, added for convenience, not opinionation
        if(!isset($this->provider_registry)) {
            $this->provider_registry = ProviderRegistry::instance();
        }

        // pre-register provider packages if they are loaded from composer
        // instances are only created when used so there's no overhead here in loading
        $providers = [
            'AxlCore\Providers\Gemini\GeminiProvider',
            'AxlCore\Providers\OpenAi\OpenAiProvider',
            'AxlCore\Providers\Anthropic\AnthropicProvider',
        ];

        foreach($providers as $provider) {
            if(class_exists($provider)) {
                $this->provider_registry->register($provider, fn() => new $provider());
            }
        }
    }

    public static function build(string $provider) : Axl
    {
        return (new self())
            ->setProvider($provider);
    }

    /**
     * @throws InvalidProviderRegistryException
     */
    public function setProvider(string $provider) : self
    {
        $this->provider = $this->provider_registry->getProvider($provider);

        return $this;
    }

    public function use(string $model, ?string $version = null) : self
    {
        if(!isset($this->provider)) {
            throw new InvalidProviderRegistryException("Set the getProvider before calling this method");
        }

        $curr = $this->provider->select($model, $version);
        if($curr->modelType() !== ModelType::CHAT) {
            throw new \Exception("The use function doesn't support this model type");
        }

        return $this;
    }

    public function useFile(string $file) : self
    {
        throw new \Exception("Needs to implement, may be refactored out");
    }

    public function useAudio(string $model) : self
    {
        throw new \Exception("Needs to implement, may be refactored out");
    }

    public function apiKey(string $key) : self
    {
        if(!isset($this->provider)) {
            throw new InvalidProviderRegistryException("Set the getProvider before calling this method");
        }

        $this->provider->setApiKey($key);

        return $this;
    }

    public function options(array $options) : self
    {
        $this->provider->setOptions($options);

        return $this;
    }

    public function headers(array $headers, array $options = [
        'append_merge' => false,
        'unique_headers' => false,
    ]) : self {
        // add additional headers
        $this->header_options = $options;
        $this->headers = $headers;

        return $this;
    }

    public function make() : AxlCore
    {
        $this->provider->setupHeaders($this->headers, $this->header_options);

        return new AxlCore(
            new PayloadBuilder($this->provider),
            $this->http_client,
            $this->provider,
            $this->stream_factory,
            $this->request_factory,
        );
    }

    public function getRegistry() : ProviderRegistry
    {
        return $this->provider_registry;
    }
}