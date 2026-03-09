<?php
namespace AxlCore\Contracts;

use Psr\Http\Message\ResponseInterface;

interface DispatcherInterface
{
    public function dispatch(string $endpoint, array $payload, array $headers) : ResponseInterface;
}