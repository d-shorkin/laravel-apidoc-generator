<?php

namespace Mpociot\ApiDoc\Postman;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class CollectionWriter
{
    /**
     * @var Collection
     */
    private $routeGroups;

    /**
     * @var string
     */
    private $baseUrl;
    /**
     * @var ItemGenerator
     */
    private $itemGenerator;

    /**
     * CollectionWriter constructor.
     *
     * @param Collection $routeGroups
     * @param $baseUrl
     * @param ItemGenerator $itemGenerator
     */
    public function __construct(Collection $routeGroups, $baseUrl, ItemGenerator $itemGenerator)
    {
        $this->routeGroups = $routeGroups;
        $this->baseUrl = $baseUrl;
        $this->itemGenerator = $itemGenerator;
    }

    public function getCollection()
    {
        try {
            URL::forceRootUrl($this->baseUrl);
        } catch (\Error $e) {
            echo "Warning: Couldn't force base url as your version of Lumen doesn't have the forceRootUrl method.\n";
            echo "You should probably double check URLs in your generated Postman collection.\n";
        }

        $collection = [
            'variables' => [],
            'info' => [
                'name' => config('apidoc.postman.name') ?: config('app.name') . ' API',
                '_postman_id' => Uuid::uuid4()->toString(),
                'description' => config('apidoc.postman.description') ?: '',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $this->routeGroups->map(function ($routes, $groupName) {
                return [
                    'name' => $groupName,
                    'description' => '',
                    'item' => $routes->map(function ($route) {
                        return $this->itemGenerator->generate($route);
                    })->toArray(),
                ];
            })->values()->toArray(),
        ];

        return json_encode($collection);
    }
}
