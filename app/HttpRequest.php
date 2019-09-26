<?php

namespace App;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class HttpRequest
{
    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * HttpRequest constructor.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param string $message
     *
     * @return self
     */
    public static function raw(string $message)
    {
        return new static(
            self::parseHttpMessage($message)
        );
    }

    /**
     * @param string $message
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected static function parseHttpMessage(string $message)
    {
        $headers = self::parseHeaders($message);
        list ($method, $path) = explode(" ", $headers[0]);
        $body = trim(substr($message, strpos($message, "\r\n\r\n")));
        $host = $headers['Host'];
        $scheme = "http://";

        return new Request($method, "{$scheme}{$host}{$path}", $headers, last(explode("\n\n", $body)));
    }

    /**
     * @return string
     */
    public function logFormat(): string
    {
        $request = $this->getRequest();

        return "  <fg=cyan> <== </>\t<fg=green>" . $request->getMethod() . "</>\t"
            . $request->getUri()->getHost() . "\t"
            . "<fg=green> => </> <fg=red>" . $request->getUri()->getPath() . "</>";
    }

    /**
     * @param string $message
     *
     * @return array
     */
    protected static function parseHeaders(string $message)
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

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
