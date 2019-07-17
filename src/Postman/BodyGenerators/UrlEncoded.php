<?php


namespace Mpociot\ApiDoc\Postman\BodyGenerators;


class UrlEncoded extends FormData
{
    /**
     * Returns list of custom headers
     * If body doesn't need custom headers returns empty array
     *
     * Example: ['Content-Type' => 'application/json']
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
    }
}