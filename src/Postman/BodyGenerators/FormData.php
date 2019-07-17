<?php


namespace Mpociot\ApiDoc\Postman\BodyGenerators;


use Mpociot\ApiDoc\Postman\ValueFaker;

class FormData implements GeneratorInterface
{
    /**
     * @var array
     */
    private $route;
    /**
     * @var string
     */
    private $requiredText;
    /**
     * @var ValueFaker
     */
    private $faker;

    /**
     * FormData constructor.
     * @param array $route
     * @param ValueFaker $faker
     * @param string $requiredText
     */
    public function __construct(array $route, ValueFaker $faker, string $requiredText = '[REQUIRED] ')
    {
        $this->route = $route;
        $this->requiredText = $requiredText;
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
        return 'formdata';
    }

    /**
     * Returns postman body data
     *
     * @return string|array
     */
    public function getContent()
    {
        if (!isset($this->route['bodyParameters'])) {
            return [];
        }

        $params = [];

        foreach ($this->route['bodyParameters'] as $key => $param) {
            $description = (isset($param['description']) && $param['description']) ? $param['description'] : "";

            if (isset($param['required']) && $param['required']) {
                $description = $this->requiredText . $description;
            }

            if (isset($param['value'])) {
                $value = $param['value'];
            } else {
                $value = $this->faker->generate($key, $description, isset($param['type']) ? $param['type'] : 'string');
            }

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $params[] = [
                "key" => $key,
                "value" => (string)$value,
                "type" => "text",
                "description" => $description
            ];
        }

        return $params;
    }
}