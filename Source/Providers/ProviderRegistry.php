<?php
namespace AxlCore\Providers;

use AxlCore\Contracts\ProviderInterface;
use AxlCore\Exceptions\Provider\InvalidProviderRegistryException;

class ProviderRegistry
{
    protected static ProviderRegistry $instance;

    public function __construct(
        protected array $providers = [],
    ){
    }

    public static function instance() : ProviderRegistry
    {
        return self::$instance ?? self::$instance = new ProviderRegistry();
    }

    public function register(string $name, callable $provider) : void
    {
        $curr = $provider();
        if(!$curr instanceof ProviderInterface) {
            throw new InvalidProviderRegistryException("Provider must be of type " . ProviderInterface::class);
        }

        $curr->initialize();

        $this->providers[$name] = $curr;
    }

    public function getProvider(string $name) : ProviderInterface
    {
        return $this->providers[$name] ?: throw new InvalidProviderRegistryException("Provider isn't registered in the registry");
    }
}