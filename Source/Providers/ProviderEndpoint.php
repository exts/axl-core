<?php
namespace AxlCore\Providers;

final class ProviderEndpoint
{
    /**
     * @param string $type
     * @param string $path
     */
    public function __construct(
        public string $type,
        public string $path,
    ){

    }
}