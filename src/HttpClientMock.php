<?php

namespace Afonso\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class HttpClientMock implements ClientInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $delegate;

    /**
     * The set of requests that have been sent through the client.
     *
     * @var \Psr\Http\Message\RequestInterface[]
     */
    protected $requestContainer = [];

    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    protected $mockHandler;

    public function __construct(array $config = [])
    {
        $this->initializeDelegate($options);
    }

    /**
     * {@inheritDoc}
     */
    public function send(RequestInterface $request, array $options = [])
    {
        return $this->delegate->send($request, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function sendAsync(RequestInterface $request, array $options = [])
    {
        return $this->delegate->sendAsync($request, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function request($method, $uri, array $options = [])
    {
        return $this->delegate->request($method, $uri, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function requestAsync($method, $uri, array $options = [])
    {
        return $this->delegate->requestAsync($method, $uri, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig($option = null)
    {
        return $this->delegate->getConfig($option);
    }

    /**
     * Check whether a request was made through this client.
     *
     * @param \Psr\Http\Message\RequestInterface
     * @return bool
     */
    public function requestWasMade(RequestInterface $assertedRequest)
    {
        foreach ($this->requestContainer as $request) {
            if ($request->getUri() !== $assertedRequest->getUri()) {
                continue;
            }

            if ($request->getMethod() !== $assertedRequest->getMethod()) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function initializeDelegate(array $options)
    {
        $this->requestContainer = [];
        $this->mockHandler = new MockHandler([]);
        $history = Middleware::history($this->requestContainer);
        $stack = HandlerStack::create($this->mockHandler);
        $stack->push($history);
        $options['handler'] = $stack;
        $this->delegate = new Client($options);
    }
}
