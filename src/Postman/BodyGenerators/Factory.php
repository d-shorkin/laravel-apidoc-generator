<?php


namespace Mpociot\ApiDoc\Postman\BodyGenerators;

use Mpociot\ApiDoc\Postman\ValueFaker;

class Factory
{
    /**
     * @var ValueFaker
     */
    private $faker;

    /**
     * Factory constructor.
     * @param ValueFaker $faker
     */
    public function __construct(ValueFaker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Creates generators
     *
     * @param array $route
     * @return GeneratorInterface
     */
    public function create(array $route): GeneratorInterface
    {
        switch ($this->getBodyType($route)) {
            case 'json':
                return new Json($route, $this->faker);
            case 'urlencoded':
                return new UrlEncoded($route, $this->faker);
            case 'formdata':
                return new FormData($route, $this->faker);
            default:
                return new EmptyBody();
        }
    }

    /**
     * Returns body type string
     *
     * @param array $route
     * @return string
     */
    private function getBodyType(array $route)
    {
        if (!isset($route['bodyParameters']) || empty($route['bodyParameters'])) {
            return 'none';
        }

        foreach ($route['bodyParameters'] as $key => $param) {
            if (strpos($key, '.') !== false) {
                return 'json';
            }
        }

        return $this->getMethod($route) !== 'POST' ? 'urlencoded' : 'formdata';
    }

    /**
     * Returns method from route
     *
     * @param $route
     * @return string
     */
    private function getMethod($route): string
    {
        return $route['methods'][0];
    }
}