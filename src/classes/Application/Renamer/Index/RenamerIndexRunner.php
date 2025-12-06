<?php

declare(strict_types=1);

namespace Application\Renamer\Index;

use DBHelper;
use Application\Renamer\DataColumnInterface;
use Application\Renamer\RenamingManager;

class RenamerIndexRunner
{
    private RenamingManager $manager;
    private string $search;

    /**
     * @var DataColumnInterface[]
     */
    private array $columns;
    private bool $caseSensitive;

    /**
     * @param RenamingManager $manager
     * @param string $search
     * @param string[] $columnIDs
     */
    public function __construct(RenamingManager $manager, string $search, array $columnIDs, bool $caseSensitive)
    {
        $this->manager = $manager;
        $this->search = $search;
        $this->columns = $this->resolveColumns($columnIDs);
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @param string[] $columnIDs
     * @return DataColumnInterface[]
     */
    private function resolveColumns(array $columnIDs): array
    {
        $result = array();
        foreach($columnIDs as $id) {
            $result[] = $this->manager->getColumns()->getByID($id);
        }

        return $result;
    }

    public function indexResults() : void
    {
        DBHelper::deleteRecords(RenamerIndex::TABLE_NAME);

        foreach($this->columns as $column) {
            $column->indexEntries($this->search, $this->caseSensitive);
        }
    }
}
