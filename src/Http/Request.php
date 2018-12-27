<?php

namespace Chocofamily\SmartHttp\Http;

use Chocofamily\SmartHttp\CircuitBreaker;
use function GuzzleHttp\Promise\unwrap;
use Phalcon\Cache\BackendInterface;
use Phalcon\Config;
use Phalcon\Di\Injectable;

class Request extends Injectable
{
    const GUZZLE_SUCCESS_STATE = 'fulfilled';
    const HTTP_METHODS         = [
        'GET'     => 1,
        'POST'    => 2,
        'PUT'     => 3,
        'DELETE'  => 4,
        'OPTIONS' => 5,
    ];

    /** @var \Chocofamily\SmartHttp\Client */
    private $httpClient;

    /** @var string */
    private $serviceName;

    /**
     * Доступные методы для отправки запроса
     * Значение означает каким параметром отправлять
     *
     * @var array
     */
    private $methods = [
        'GET'     => 'query',
        'HEAD'    => 'query',
        'POST'    => 'form_params',
        'PUT'     => 'form_params',
        'PATCH'   => 'form_params',
        'DELETE'  => 'query',
        'OPTIONS' => 'query',
    ];

    public function __construct(
        Config $config,
        BackendInterface $cache
    ) {
        $this->httpClient = new \Chocofamily\SmartHttp\Client($config, $cache);
        $this->serviceName = $config->get('serviceName');
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @param array  $data
     *
     * @param null   $serviceName
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $method, string $uri, $data = [], $serviceName = null)
    {
        $options = $this->generateOptions($serviceName);
        $options[$this->methods[$method]] = $data;

        return $this->httpClient->request($method, $uri, $options);
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @param array  $data
     * @param null   $serviceName
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(string $method, string $uri, $data = [], $serviceName = null)
    {
        $options = $this->generateOptions($serviceName);
        $options[$this->methods[$method]] = $data;

        return $this->httpClient->requestAsync($method, $uri, $options);
    }

    /**
     * @param      $routes
     *
     * @param null $serviceName
     *
     * @return array
     * @throws \Throwable
     */
    public function sendMultiple($routes, $serviceName = null)
    {
        $options = $this->generateOptions($serviceName);
        $promises = [];

        foreach ($routes as $route) {
            $promises[] = $this->httpClient->requestAsync('GET', $route, $options);
        }

        return unwrap($promises);
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    private function generateOptions($serviceName)
    {
        $options = [];

        if (isset($serviceName) || isset($this->serviceName)) {
            $options[CircuitBreaker::CB_TRANSFER_OPTION_KEY] = $serviceName ?: $this->serviceName;
        }

        return $options;
    }
}
