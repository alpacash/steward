<?php

namespace App;

/**
 * Class RawHttp
 * @package App
 */
class RawHttp
{
    /**
     * @var string
     */
    protected $raw;

    /**
     * RawHttp constructor.
     *
     * @param string $raw
     */
    public function __construct(string $raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = array_map(function ($value) {
            return array_map("trim", explode(":", $value, 2));
        }, array_filter(array_map("trim", explode("\n", current(explode("\r\n\r\n", $this->raw))))));

        $result = [0 => $headers[0][0]];
        unset($headers[0]);

        foreach ($headers as $header) {
            list($key, $value) = $header + ['', ''];
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        $parts = explode("\r\n", $this->raw);
        unset($parts[0]);

        return implode("\r\n", $parts);
    }
}
