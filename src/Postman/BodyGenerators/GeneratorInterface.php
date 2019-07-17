<?php


namespace Mpociot\ApiDoc\Postman\BodyGenerators;


interface GeneratorInterface
{
    /**
     * Returns list of custom headers
     * If body doesn't need custom headers returns empty array
     *
     * Example: ['Content-Type' => 'application/json']
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Returns postman body mode
     *
     * Example: raw, urlencoded, formdata
     *
     * @return string
     */
    public function getMode(): string;

    /**
     * Returns postman body data
     *
     * @return string|array
     */
    public function getContent();

}