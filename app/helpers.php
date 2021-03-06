<?php

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;

function respond_json($responseData, int $statusCode = 200, array $headers = [])
{
    return Message::toString(new Response(
        $statusCode,
        array_merge($headers, ['Content-Type' => 'application/json']),
        json_encode($responseData, JSON_INVALID_UTF8_IGNORE)
    ));
}

function respond_html(string $html, int $statusCode = 200, array $headers = [])
{
    return Message::toString(new Response(
        $statusCode,
        array_merge($headers, ['Content-Type' => 'text/html']),
        $html
    ));
}
