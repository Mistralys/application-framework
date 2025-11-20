<?php

declare(strict_types=1);

namespace AppFrameworkTests\DBHelper;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\NewsCentral\NewsFilterCriteria;

final class FilterCriteriaTest extends ApplicationTestCase
{
    public function test_placeholdersWithOrWithoutColon() : void
    {
        $this->filters->addPlaceholder(':foo', 'foo-value');
        $this->filters->addPlaceholder('bar', 'bar-value');

        $placeholders = $this->filters->getQueryVariables();
        $msg = print_r($placeholders, true);

        $this->assertArrayHasKey(':foo', $placeholders, $msg);
        $this->assertArrayHasKey(':bar', $placeholders, $msg);
    }

    public function test_resetQueryVariables() : void
    {
        $this->filters->addPlaceholder(':foo', 'foo-value');
        $this->filters->addPlaceholder('bar', 'bar-value');

        $this->assertNotEmpty($this->filters->getQueryVariables());

        $this->filters->resetQueryVariables();

        $this->assertEmpty($this->filters->getQueryVariables());
    }

    private NewsFilterCriteria $filters;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filters = AppFactory::createNews()->getFilterCriteria();
    }
}
