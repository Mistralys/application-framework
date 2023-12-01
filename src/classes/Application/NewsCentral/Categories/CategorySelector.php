<?php

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use Application\AppFactory;
use Application_Formable_RecordSelector;
use Application_Formable_RecordSelector_Entry;

class CategorySelector extends Application_Formable_RecordSelector
{
    protected function init(): void
    {
        $this->makeMultiple();
        $this->makeMultiselect();
    }

    public function createCollection() : CategoriesCollection
    {
        return AppFactory::createNews()->createCategories();
    }

    protected function configureFilters(): void
    {
    }

    protected function configureEntry(Application_Formable_RecordSelector_Entry $entry): void
    {

    }
}
