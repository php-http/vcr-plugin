<?php

namespace Http\Client\Plugin\Vcr;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Plugin\PluginClient;
use Psr\Http\Message\RequestInterface;

class VcrClient implements HttpClient, HttpAsyncClient
{
    /**
     * @var PluginClient
     */
    private $client;

    public function __construct($client, Vcr $vcr)
    {
        $this->client = new PluginClient($client, [new VcrPlugin($vcr)]);
    }

    public function sendRequest(RequestInterface $request)
    {
        return $this->client->sendRequest($request);
    }

    public function sendAsyncRequest(RequestInterface $request)
    {
        return $this->client->sendAsyncRequest($request);
    }
}
