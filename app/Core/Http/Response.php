<?php
namespace App\Core\Http;

class Response
{
    protected string $body;

    protected int $statusCode = 200;

    protected array $headers = [];

    protected array $removeHeaders = [];

    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    public function getBody()
    {
        return $this->body ?? '';
    }

    public function withStatus($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withJson($body)
    {
        $this->withHeader('Content-Type', 'application/json');
        $this->body = json_encode($body);

        return $this;
    }

    public function withHeader($name, $value)
    {
        $this->headers[] = [$name, $value];
        return $this;
    }

    public function removeHeader($name)
    {
        $this->removeHeaders[] = $name;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function send()
    {
        foreach ($this->removeHeaders as $headerName) {
            header_remove($headerName);
        }

        header(sprintf(
            'HTTP/%s %s %s',
            '1.1',
            $this->getStatusCode(),
            ''
        ));

        foreach ($this->getHeaders() as $header) {
            header($header[0] . ': ' . $header[1]);
        }

        return $this->getBody();
    }

    public function __toString()
    {
        return $this->send();
    }
}