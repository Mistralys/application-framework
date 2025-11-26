<?php

declare(strict_types=1);

namespace Application\Renamer\Index;

use AppUtils\ConvertHelper\JSONConverter;
use DBHelper;
use DBHelper_BaseRecord;
use Application\Renamer\DataColumnInterface;
use Application\Renamer\RenamingManager;

class RenamerRecord extends DBHelper_BaseRecord
{
    private ?DataColumnInterface $dataColumn = null;

    public function getColumnID() : string
    {
        return $this->getRecordStringKey(RenamerIndex::COL_COLUMN_ID);
    }

    public function getColumn(): DataColumnInterface
    {
        if(!isset($this->dataColumn)) {
            $this->dataColumn = RenamingManager::getInstance()->getColumns()->getByID($this->getColumnID());
        }

        return $this->dataColumn;
    }

    protected function _dispose(): void
    {
        parent::_dispose();

        $this->dataColumn = null;
    }

    /**
     * Loads the original matched text from the database
     * using the stored primary values.
     *
     * @return string
     */
    public function loadMatchedText() : string
    {
        $column = $this->getColumn();

        return DBHelper::createFetchKey($column->getColumnName(), $column->getTableName())
            ->whereValues($this->getPrimaryValues())
            ->fetchString();
    }

    public function getHash() : string
    {
        return $this->getRecordStringKey(RenamerIndex::COL_HASH);
    }

    public static function insert(DataColumnInterface $column, array $data) : void
    {
        $columnName = $column->getColumnName();

        // We are only using the matched text for hash calculation,
        // but do not keep it to avoid the overhead. We can fetch
        // it again later if needed, using the primary values.
        $matchedText = $data[$columnName] ?? '';
        unset($data[$columnName]);

        DBHelper::insertDynamic(
            RenamerIndex::TABLE_NAME,
            array(
                RenamerIndex::COL_COLUMN_ID => $column->getID(),
                RenamerIndex::COL_HASH => md5($matchedText),
                RenamerIndex::COL_PRIMARY_VALUES => JSONConverter::var2json($data)
            )
        );
    }

    public function countMatches() : int
    {
        return DBHelper::fetchCount(
            "SELECT COUNT(*) AS `count` FROM ".RenamerIndex::TABLE_NAME." WHERE ".RenamerIndex::COL_HASH." = :hash",
            array(
                'hash' => $this->getHash()
            )
        );
    }

    public function processReplace(string $search, string $replace, bool $caseSensitive=true) : void
    {
        $column = $this->getColumn();
        $data = $this->getPrimaryValues();
        $original = $this->loadMatchedText();

        if ($caseSensitive) {
            $newText = str_replace($search, $replace, $original);
        } else {
            $newText = str_ireplace($search, $replace, $original);
        }

        $data[$column->getColumnName()] = $newText;

        DBHelper::updateDynamic(
            $column->getTableName(),
            $data,
            $column->getPrimaryColumns()
        );
    }

    public function getPrimariesJSON() : string
    {
        return $this->getRecordStringKey(RenamerIndex::COL_PRIMARY_VALUES);
    }

    /**
     * @return array<int|string,string>
     */
    public function getPrimaryValues() : array
    {
        $primaries = JSONConverter::json2array($this->getPrimariesJSON());

        $values = array();
        foreach($this->getColumn()->getPrimaryColumns() as $key) {
            $values[$key] = $primaries[$key] ?? '';
        }

        return $values;
    }

    public function toExportColumns() : array
    {
        return array(
            $this->getColumnID(),
            $this->getHash(),
            $this->getPrimariesJSON(),
            $this->loadMatchedText()
        );
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }

    public function getLabel(): string
    {
        return $this->getHash();
    }
}
