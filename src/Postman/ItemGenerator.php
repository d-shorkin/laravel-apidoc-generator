<?php


namespace Mpociot\ApiDoc\Postman;

use Mpociot\ApiDoc\Postman\BodyGenerators\Factory;
use Mpociot\ApiDoc\Postman\BodyGenerators\GeneratorInterface as Body;
use Ramsey\Uuid\Uuid;

class ItemGenerator
{
    /**
     * @var ValueFaker
     */
    private $faker;
    /**
     * @var Factory
     */
    private $bodyFactory;

    /**
     * ItemGenerator constructor.
     * @param ValueFaker $faker
     * @param Factory $bodyFactory
     */
    public function __construct(ValueFaker $faker, Factory $bodyFactory)
    {
        $this->faker = $faker;
        $this->bodyFactory = $bodyFactory;
    }

    /**
     * Generate
     *
     * @param $route
     * @return array
     * @throws \Exception
     */
    public function generate($route): array
    {
        $body = $this->bodyFactory->create($route);

        $chaiSubset = explode("\n", file_get_contents(__DIR__ . '/chai-subset.js'));

        return [
            "name" => $route['title'] ?: url($route['uri']),
            "event" => [
                [
                    "listen" => "test",
                    "script" => [
                        "id" => Uuid::uuid4()->toString(),
                        "exec" => array_merge(
                            [
                                "const chai = require(\"chai\");",
                            ],
                            $chaiSubset,
                            [
                                "chai.use(chaiSubset);",
                                "pm.test(\"Status code is 200\", function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test(\"Body is correct\", function () {",
                                "    chai.expect(pm.response.json()).to.containSubset("
                            ],
                            $this->getResponseForTest($route),
                            [
                                "    )",
                                "});"
                            ])

                    ]
                ]
            ],
            "request" => [
                "method" => $this->getMethod($route),
                "header" => $this->getHeaders($route, $body),
                "body" => [
                    'mode' => $body->getMode(),
                    $body->getMode() => $body->getContent()
                ],
                "url" => $this->getUrl($route)
            ],
        ];
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

    /**
     * @param $route
     * @return array
     */
    private function getResponseForTest($route): array
    {
        if (!$this->hasResponse($route)) {
            return [];
        }

        $response = json_encode(json_decode($route['response'][0]['content'], true), JSON_PRETTY_PRINT);

        return explode("\n", $response);
    }

    private function hasResponse($route): bool
    {
        return isset($route['response'][0]);
    }

    /**
     * Returns list of headers
     *
     * @param $route
     * @param Body $body
     * @return array
     */
    private function getHeaders($route, Body $body): array
    {
        $headers = [];

        foreach (array_merge($route['headers'], $body->getHeaders()) as $header => $value) {
            $headers[] = [
                "key" => $header,
                "name" => $header,
                "value" => $value,
                "type" => "text"
            ];
        }

        return $headers;
    }

    /**
     * Returns raw url
     *
     * @param $route
     * @return string
     */
    private function getRawUrl($route): string
    {
        $uri = $route['uri'];

        $params = isset($route['uriParams']) ? $route['uriParams'] : [];

        foreach ($params as $key => $param) {
            $uri = str_replace('{' . $key . '}', $param, $uri);
        }

        $url = '{{api_url}}/' . ltrim($uri, '/');

        if (!isset($route['queryParameters']) || !$route['queryParameters']) {
            return $url;
        }

        return $url . "?" . http_build_query(array_map(function ($item) {
                return isset($item['value']) ? $item['value'] : '';
            }, $route['queryParameters']));
    }

    /**
     * Build url associative array
     *
     * @param $route
     * @param string $requiredText
     * @return array
     */
    private function getUrl($route, $requiredText = '[REQUIRED] '): array
    {
        $raw = $this->getRawUrl($route);

        $parsed = parse_url($raw);

        $build = [
            'raw' => $raw,
            'host' => ['{{api_url}}']
        ];

        if (isset($parsed['path'])) {
            $build['path'] = array_slice(explode('/', trim($parsed['path'], '/')), 1);
        }

        if (isset($parsed['user'])) {
            $build['auth'] = [
                'user' => $parsed['user']
            ];

            if (isset($parsed['pass'])) {
                $build['auth']['password'] = $parsed['pass'];
            }
        }

        if (isset($parsed['fragment'])) {
            $build['hash'] = $parsed['fragment'];
        }

        if (!isset($route['queryParameters']) || !$route['queryParameters']) {
            return $build;
        }

        $build['query'] = [];

        foreach ($route['queryParameters'] as $param => $options) {
            $key = $param;
            $description = isset($options['description']) ? $options['description'] : '';

            if (isset($options['required']) && $options['required']) {
                $description = $requiredText . $description;
            }

            if (isset($options['value']) && $options['value'] === null) {
                $value = $options['value'];
            } else {
                $value = $this->faker->generate($key, $description);
            }

            $build['query'][] = compact('key', 'value', 'description');
        }

        return $build;
    }


}