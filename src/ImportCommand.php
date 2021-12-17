<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{

    /** @var RecipeImporter */
    private RecipeImporter $importer;

    public function __construct(string $name = null, RecipeImporter $importer)
    {
        parent::__construct($name);
        $this->importer = $importer;
    }

    /** @var string */
    protected static $defaultName = 'app:import';


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->importer->importRecipe(555);
    }
}
