<?php


namespace Mpociot\ApiDoc\Postman\BodyGenerators;


use Mpociot\ApiDoc\Postman\ValueFaker;

class Json implements GeneratorInterface
{
    /**
     * @var array
     */
    private $route;
    /**
     * @var ValueFaker
     */
    private $faker;

    /**
     * JsonBodyGenerator constructor.
     * @param array $route
     * @param ValueFaker $faker
     */
    public function __construct(array $route, ValueFaker $faker)
    {
        $this->route = $route;
        $this->faker = $faker;
    }

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
        return ['Content-Type' => 'application/json'];
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
        if (!isset($this->route['bodyParameters'])) {
            return '{}';
        }

        $data = [];

        foreach ($this->route['bodyParameters'] as $pathString => $options) {
            $current = &$data;
            foreach ($this->splitPath($pathString) as $key) {
                if (!isset($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }

            if (isset($options['value'])) {
                $value = $options['value'];
            } else {
                $value = $this->faker->generate(
                    $pathString,
                    isset($options['description']) ? $options['description'] : '',
                    isset($options['type']) ? $options['type'] : 'string'
                );
            }

            $current = $value;
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @param string $path
     * @return array
     */
    private function splitPath(string $path)
    {
        $path = str_replace('[]', '[0]', $path);

        $path = str_replace(['[', ']'], ['.', ''], $path);

        return explode('.', $path);
    }
}