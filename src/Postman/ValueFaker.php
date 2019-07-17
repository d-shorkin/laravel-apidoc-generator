<?php


namespace Mpociot\ApiDoc\Postman;


class ValueFaker
{
    public function generate(string $name, string $description = null, $type = 'text')
    {
        return "test";
    }
}