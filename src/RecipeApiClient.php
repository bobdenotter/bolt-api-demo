<?php

namespace App;

use GuzzleHttp\Client;

class RecipeApiClient
{
    public const ENDPOINT_RECIPE_ID = 'www.themealdb.com/api/json/v1/1/lookup.php?i=';

    /** @var Client */
    private Client $client;
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->client = new Client();
        $this->projectDir = $projectDir;
    }

    public function fetchRecipe(int $id): array
    {
        $endpoint = $this->getRecipeEndpoint($id);
        $response = $this->client->get($endpoint);

        if ($response->getStatusCode() !== 200){
            throw new \Exception('API not reachable');
        }

        $recipeArray = json_decode($response->getBody()->getContents(), true);

        return current($recipeArray['meals']);
    }

    private function getRecipeEndpoint(int $id): string
    {
        return sprintf("%s%s", self::ENDPOINT_RECIPE_ID, $id);
    }


    public function downloadImage(string $url): string
    {
        $filename = uniqid() . '.jpg';

        dump($url);
        dump($this->projectDir . '/public/files/' . $filename);

        $this->client->get($url, [
            'sink' => $this->projectDir . '/public/files/' . $filename
        ]);


        return $filename;
    }
}
