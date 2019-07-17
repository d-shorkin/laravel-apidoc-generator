<?php


namespace Mpociot\ApiDoc\Postman\BodyGenerators;


class EmptyBody implements GeneratorInterface
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
        return [];
    }

    /**
     * Returns postman body mode
     *
     * Example: raw, urlencoded, formdata
     *
     * @return string
     */
    public function getMode(): string
    {
        return 'raw';
    }

    /**
     * Returns postman body data
     *
     * @return string|array
     */
    public function getContent()
    {
        return '';
    }
}