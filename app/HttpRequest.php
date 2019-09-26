<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Proxy\Proxy;

class HttpRequest
{

    /**
     * @param string $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function raw(string $message)
    {
        return (new static())->parseHttpMessage($message);
    }

    /**
     * @param string $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function parseHttpMessage(string $message)
    {
        $headers = $this->parseHeaders($message);
        list ($method, $path) = explode(" ", $headers[0]);

        $body = trim(substr($message, strpos($message, "\r\n\r\n")));
        $request = new Request($method, "{$headers['Host']}{$path}", $headers, last(explode("\n\n", $body)));

        // Create a guzzle client
        $guzzle = new Client();
        $proxy = new Proxy(new GuzzleAdapter($guzzle));
        $proxy->filter(new RemoveEncodingFilter());
        new \Zend\Diactoros\Request();

        return $proxy->forward($request)->to('http://127.0.0.1:80');
    }

    /**
     * @param string $message
     *
     * @return array
     */
    protected function parseHeaders(string $message)
    {
        $headers = array_map(function ($value) {
            return array_map("trim", explode(":", $value, 2));
        }, array_filter(array_map("trim", explode("\n", $message))));

        $result = [0 => $headers[0][0]];
        unset($headers[0]);

        foreach ($headers as $header) {
            list($key, $value) = $header + ['', ''];
            $result[$key] = $value;
        }

        return $result;
    }
}
