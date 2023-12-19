<?php

class CurlPost
{
    private $url;
    private $options;

    /**
     * @param string $url Request URL
     * @param array $options cURL options
     */
    public function __construct(string $url, array $options = [])
    {
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * Get the response
     * @return string
     * @throws \RuntimeException On cURL error
     */
    public function __invoke(array $post, array $header)
    {
        $ch = \curl_init($this->url);

        foreach ($this->options as $key => $val) {
            \curl_setopt($ch, $key, $val);
        }

        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $header);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, json_encode($post));
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = \curl_exec($ch);
        $error = \curl_error($ch);
        $errno = \curl_errno($ch);

        if (\is_resource($ch)) {
            \curl_close($ch);
        }

        if (0 !== $errno) {
            throw new \RuntimeException($error, $errno);
        }

        return $response;
    }
}