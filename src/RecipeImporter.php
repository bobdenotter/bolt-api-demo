<?php

namespace App;

use Bolt\Entity\Content;
use Bolt\Entity\Field\TextField;
use Bolt\Enum\Statuses;
use Bolt\Factory\ContentFactory;
use Cocur\Slugify\Slugify;

class RecipeImporter
{
    /** @var RecipeApiClient */
    private RecipeApiClient $client;

    /** @var ContentFactory */
    private ContentFactory $factory;

    public function __construct(RecipeApiClient $client, ContentFactory $factory)
    {
        $this->client = $client;
        $this->factory = $factory;
    }

    public function importRecipe(int $id): void
    {
        $recipe = collect($this->client->fetchRecipe($id));

        $recipe = $recipe->keyBy(function($value, $key) {
           return self::recipeFieldMap()[$key] ?? null;
        })->filter();

        $record = $this->factory->upsert('recipes',  [ 'recipe_id' =>  $id ]);

        foreach($recipe as $name => $value) {
            $record->setFieldValue($name, $value); // default

            if ($record->getDefinition()->get('fields')[$name]['type'] === 'image') {
                $filename = $this->client->downloadImage($value);
                $record->setFieldValue($name, ['filename' => $filename]);
            }

            if ($record->getDefinition()->get('fields')[$name]['type'] === 'collection') {
                $this->importIngredients($name, $value);
            }
        }

        /*
        $record->setFieldValue('collection', [
            ['ingredient' => 'tomatoes'],
            ['ingredient' => 'lemons'],
        ];
        $record->setFieldValue('collection', [
            ['ingredient' => [ 'type'=>'tomatoes', 'quantity'=>'50'] ],
        ];
        */

        $slugify = Slugify::create();
        $record->setFieldValue('slug', $slugify->slugify($recipe['title']));

        $record->setPublishedAt(new \DateTime());
        $record->setStatus(Statuses::DRAFT);

        $this->factory->save($record);
    }

    private static function recipeFieldMap(): array
    {
        return [
            'strMeal' => 'title',
            'strMealThumb' => 'photo',
            'strInstructions' => 'instructions',
            'idMeal' => 'recipe_id'
        ];
    }

    private function importIngredients(Content $record, ?int $name, $value)
    {
        // $value: ['tomatoes', 'lemons', 'pumpkin']

        $collection = $record->getField('ingredients');

        $fields = [];
        foreach($value as $ingredient) {
            $field = new TextField();
            $field->setValue($ingredient);

            $fields[] = $field;
        }

        $collection->setValue($fields);
    }
}
