<?php
namespace AxlCore\Dispatcher;

use AxlCore\Contracts\DispatcherInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Dispatcher implements DispatcherInterface
{
    protected int $stream_read_size = 8192;

    public function __construct(
        protected string $base_url,
        protected ClientInterface $client,
        protected StreamFactoryInterface $stream_factory,
        protected RequestFactoryInterface $request_factory,
    ){
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function dispatch(string $endpoint, array $payload, array $headers) : ResponseInterface
    {
        if(empty($payload)) {
            throw new \Exception("Trying to dispatch an empty paypload");
        }

        $request = $this->request_factory
            ->createRequest('POST', $this->pathNormalizer($endpoint));

        // setup headers
        foreach($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        // set payload
        $request = $request->withBody($this->stream_factory
            ->createStream(json_encode($payload)));

        $response = $this->client->sendRequest($request);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function dispatchStream(string $endpoint, array $payload, array $headers) : \Generator
    {
        $headers['Accept'] = 'text/event-stream';

        $response = $this->dispatch($endpoint, $payload, $headers);
        $response_body = $response->getBody();

        $buffer = '';
        while(!$response_body->eof()) {
            $chunk = $response_body->read($this->stream_read_size);
            if($chunk === '') {
                usleep(10_000);
                continue;
            }

            $buffer .= $chunk;

            // SSE events are separated by a blank line: \n\n
            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $event_block = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);

                // Each event block has lines; "data:" lines contain payload
                foreach (explode("\n", $event_block) as $line) {
                    $line = rtrim($line, "\r");

                    // ignore comments/empty lines and non-data lines
                    if (!str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $data_line = ltrim(substr($line, 5)); // after "data:"

                    // Some providers send a done sentinel
                    if ($data_line === '[DONE]') {
                        echo "DONE\n";
                        exit(0);
                    }

                    // Print raw SSE payload (often JSON)
                    yield $this->parseStreamData($data_line);
                }
            }
        }
    }

    protected function pathNormalizer(string $endpoint) : string
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($endpoint, '/');
    }

    protected function parseStreamData(string $stream) : array
    {
        return json_decode($stream, true);
    }
}