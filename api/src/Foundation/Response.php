<?php

namespace App\Foundation;

final class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $body = null;

    public function addHeader(string $name, $value) {
        $key = strtolower($name);
        $this->headers[$key][] = $value;
    }

    public function setHeader(string $name, $value) {
        $key = strtolower($name);
        $this->headers[$key] = [$value];
    }

    public function withStatusCode(int $code): Response {
        $this->statusCode = $code;
        return $this;
    }

    public function withBody($body) {
        $this->body = $body;
        return $this;
    }

    public static function json($data, $status = 200) {
        $response = new static();
        $response->addHeader("content-type", "application/json");
        return $response->withBody($data)->withStatusCode($status);
    }

    public function send() 
    {
        http_response_code($this->statusCode);
        foreach($this->headers as $key => $values) {
            $value = implode(",", $values);
            header("$key: $value");
        }

        if (is_string($this->body)) {
            echo (string)$this->body;
        }
        else if (is_array($this->body) || is_object($this->body))
        {
            echo json_encode($this->body);
        }
    }
}