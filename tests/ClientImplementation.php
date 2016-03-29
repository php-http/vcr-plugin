<?php

namespace Http\Client\Plugin\Vcr;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;

interface ClientImplementation extends HttpClient, HttpAsyncClient
{

}
