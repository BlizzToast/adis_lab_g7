<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Response class to handle HTTP responses
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = "";

    /**
     * Set the HTTP status code
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    /**
     * Set a response header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        header("$name: $value");
        return $this;
    }

    /**
     * Set the response body
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        // Send headers if not already sent
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }

        // Send body
        echo $this->body;
    }

    /**
     * Redirect to another URL
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader("Location", $url);
        $this->send();
        exit();
    }

    /**
     * Send a JSON response
     */
    public function json(array $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader("Content-Type", "application/json");
        $this->setBody(json_encode($data));
        $this->send();
        exit();
    }

    /**
     * Send an error response
     */
    public function error(int $statusCode = 500, string $message = ""): void
    {
        $this->setStatusCode($statusCode);

        $errorMessages = [
            400 => "Bad Request",
            401 => "Unauthorized",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            500 => "Internal Server Error",
            503 => "Service Unavailable",
        ];

        $defaultMessage = $errorMessages[$statusCode] ?? "Error";
        $message = $message ?: $defaultMessage;

        $this->setBody("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Error $statusCode</title>
                <link rel='stylesheet' href='https://unpkg.com/terminal.css@0.7.4/dist/terminal.min.css'>
            </head>
            <body>
                <div class='container'>
                    <h1>Error $statusCode</h1>
                    <p>$message</p>
                    <p><a href='/'>Go back to home</a></p>
                </div>
            </body>
            </html>
        ");

        $this->send();
        exit();
    }
}
