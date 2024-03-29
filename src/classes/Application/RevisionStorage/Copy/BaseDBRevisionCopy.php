<?php
/**
 * @package Application
 * @subpackage Pages
 * @see \Application\RevisionStorage\Copy\BaseDBRevisionCopy
 */

namespace Application\RevisionStorage\Copy;

use Application\RevisionStorage\RevisionStorageException;
use Application_RevisionableStateless;
use BaseDBStandardizedStorage;
use AppUtils\ConvertHelper_Exception;
use DBHelper;
use DBHelper_Exception;
use JsonException;

/**
 * Handles copying entire revisions.
 *
 * It only requires the target revision record to exist (for the revision number).
 * This is done by the revision handler.
 *
 * @package Application
 * @subpackage Revisionables
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see \Application\RevisionStorage\Copy\BaseRevisionCopy
 *
 * @property BaseDBStandardizedStorage $storage
 */
abstract class BaseDBRevisionCopy extends BaseRevisionCopy
{
    public const ERROR_KEYS_MISMATCH_FOR_TARGET_DATA_SET = 620002;

    public const ERROR_NON_UNIQUE_AUTO_INCREMENT = 620003;

    public const ERROR_MISSING_TARGET_DATA_FOR_COPY = 620004;

    /**
     * @var array<string,array<string,string|int>>
     */
    protected array $autoIncrements = array();

    /**
     * Copies all records from a table from the source revision to the
     * target revision.
     *
     * @param string $table
     * @param string $revisionKey The name of the db field containing the revision number
     * @param string[] $primaryKeys Indexed array with primary field key names in the table
     * @param array<string,mixed> $values Associative array with specific values for fields (overwrites data from records)
     * @param array<string,mixed> $targetValues
     * @throws DBHelper_Exception
     *
     * @throws JsonException
     * @throws RevisionStorageException
     * @throws ConvertHelper_Exception
     */
    protected function copyRecords(string $table, string $revisionKey, array $primaryKeys, array $values = array(), array $targetValues = array()): void
    {
        $this->log(sprintf('Copying records from table [%s].', $table));

        // when values are set, it means the revision key is not enough to
        // uniquely identify a record. In this case, the additional keys
        // are set with the values. This also means that the values should
        // be set separately for the target revisionable object if this is
        // not the same as the source object.
        if (!empty($values)) {
            if (empty($targetValues)) {
                $targetValues = $values;

                if ($this->revisionable !== $this->targetRevisionable) {
                    throw new RevisionStorageException(
                        'Missing data for revision copy',
                        'When setting another revisionable as target for a copy, the [$targetValues] parameter has to be set.',
                        self::ERROR_MISSING_TARGET_DATA_FOR_COPY
                    );
                }
            }

            // make sure that the values and target values have exactly the same keys.
            $diff = array_diff(array_keys($values), array_keys($targetValues));
            if (!empty($diff)) {
                throw new RevisionStorageException(
                    'Mismatching data sets',
                    'The data sets for the source and target revisionables do not have the same keys.',
                    self::ERROR_KEYS_MISMATCH_FOR_TARGET_DATA_SET
                );
            }
        } else {
            $targetValues = array();
        }

        //$this->log('Step 1: Clearing records in the target revision.');

        $query =
            "DELETE FROM
            `" . $table . "`
        WHERE
            `" . $revisionKey . "`=:revision_key";

        $data = array();
        $data[':revision_key'] = $this->targetRevision;

        if (!empty($targetValues)) {
            foreach ($targetValues as $name => $value) {
                $query .= sprintf(
                    " AND `%s` = :%s",
                    $name,
                    $name
                );

                $data[$name] = $value;
            }
        }

        DBHelper::delete($query, $data);

        //$this->log('Step 2: Copying records.');

        $query =
            "SELECT 
            *
        FROM
            " . $table . "
        WHERE
            `" . $revisionKey . "`=:revision_key";

        $data = array();
        $data[':revision_key'] = $this->sourceRevision;

        if (!empty($values)) {
            foreach ($values as $name => $value) {
                $query .= sprintf(
                    " AND `%s` = :%s",
                    $name,
                    $name
                );

                $data[$name] = $value;
            }
        }

        $entries = DBHelper::fetchAll($query, $data);
        if (empty($entries)) {
            $this->log('No records found in table, skipping.');
            return;
        }

        if ($this->debug) {
            echo '<pre>' . print_r($table, true) . '</pre>';
            echo '<pre>' . print_r($entries, true) . '</pre>';
        }

        // ---------------------------------------------------------
        // Auto increment column handling: stores old IDs and new
        // matching auto increment values to be able to automatically
        // convert IDs, using the mapIncrementValues method.
        // ---------------------------------------------------------
        $autoColumn = null;
        foreach ($primaryKeys as $name) {
            $path = $table . '.' . $name;
            if (isset($this->autoIncrements[$path])) {
                continue;
            }

            $this->autoIncrements[$path] = false;

            if (!DBHelper::isAutoIncrementColumn($table, $name)) {
                continue;
            }

            // already found an auto increment column?
            if ($autoColumn !== null) {
                throw new RevisionStorageException(
                    'Unsupported DB configuration',
                    sprintf(
                        'The table [%s] seems to have more than one auto increment column. This is not supported.',
                        $table
                    ),
                    self::ERROR_NON_UNIQUE_AUTO_INCREMENT
                );
            }

            $this->autoIncrements[$path] = array();
            $autoColumn = $name;
        }

        $columns = array_keys($entries[0]);

        // determine if there are any existing auto increment columns that can
        // be updated with new values within this table.
        $mapped = array();
        foreach ($columns as $name) {
            $path = $table . '.' . $name;
            foreach ($this->incrementMappings as $source => $targets) {
                if (in_array($path, $targets, true)) {
                    $mapped[$name] = $source;
                }
            }
        }

        if (!empty($mapped)) {
            $this->log(sprintf(
                'Converting columns [%s] with auto increment mappings.',
                implode(', ', array_keys($mapped))
            ));
        }

        // ------------------------------------------------
        // Insert all new records
        // ------------------------------------------------
        $autoValuecount = 0;
        foreach ($entries as $entry) {
            $autoValue = null;

            $entry[$revisionKey] = $this->targetRevision;
            foreach ($targetValues as $name => $value) {
                $entry[$name] = $value;
            }

            // if there are mapped auto increment columns, replace the old
            // IDs with the new ones.
            foreach ($mapped as $name => $source) {
                $oldValue = $entry[$name];
                if (!isset($this->autoIncrements[$source][$oldValue])) {
                    continue;
                }
                $newValue = $this->autoIncrements[$source][$oldValue];
                $entry[$name] = $newValue;

                //$this->log(sprintf('Converted auto column [%s] value from [%s] to [%s].', $name, $oldValue, $newValue));
            }

            if ($autoColumn !== null) {
                $autoValue = $entry[$autoColumn];
                $entry[$autoColumn] = null; // so we can insert a new record, otherwise we'd update the previous record
            }

            if ($this->debug) {
                echo '<pre>' . print_r($entry, true) . '</pre>';
            }

            $insertID = DBHelper::insertOrUpdate(
                $table,
                $entry,
                $primaryKeys
            );

            // if this operation was an insert into a table with an auto 
            // increment column, store the old and new values, so we can
            // use them afterward wherever needed.
            if ($autoColumn !== null) {
                $this->autoIncrements[$table . '.' . $autoColumn][$autoValue] = $insertID;
                $autoValuecount++;
            }
        }

        if ($this->debug) {
            echo '<hr/>';
        }

        $this->log(sprintf('Copied [%s] records.', count($entries)));

        if ($autoValuecount > 0) {
            $this->log(sprintf(
                'Registered [%s] values for auto increment column [%s].',
                $autoValuecount,
                $autoColumn
            ));
        }
    }

    protected array $incrementMappings = array();

    /**
     * Maps the auto increment value from the source column to the
     * target column. Every source column added like this will have
     * its available values populated automatically when copying
     * the records from that table.
     *
     * Afterward, this ID collection can be used to map the IDs from
     * the source table to the new IDs that were created, to ensure
     * that any other table records depending on these IDs get the
     * correct new IDs assigned.
     *
     * @param string $source Must be specified in the notation <code>tablename.columnname</code>
     * @param string $target Must be specified in the notation <code>tablename.columnname</code>
     */
    protected function mapIncrementColumns(string $source, string $target): void
    {
        if (!isset($this->incrementMappings[$source])) {
            $this->incrementMappings[$source] = array();
        }

        $this->incrementMappings[$source][] = $target;
    }

    /**
     * Copies all records within a table, using the specified auto increment
     * table mappings: The target path is the table and auto increment column
     * for the table in which to copy records, and the source path is the
     * table to use the auto increment values from.
     *
     * Note that the source increment values have to be present already.
     *
     * @param string $targetPath Path to the target auto increment column
     * @param string $sourcePath Path to the source auto increment column
     * @param string[] $primaryColumns
     * @param array<string,mixed> $targetValues
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    protected function copyByIncrements(string $targetPath, string $sourcePath, array $primaryColumns, array $targetValues = array()): void
    {
        if (!isset($this->autoIncrements[$sourcePath])) {
            return;
        }

        $tokens = explode('.', $targetPath);
        $column = array_pop($tokens);
        $table = array_pop($tokens);
        $newValues = array_values($this->autoIncrements[$sourcePath]);
        $oldValues = array_keys($this->autoIncrements[$sourcePath]);

        $this->log(sprintf('Copying records from table [%s].', $table));

        $this->log(sprintf(
            'Using auto increment mapping from [%s].',
            $sourcePath
        ));

        // delete any existing records
        DBHelper::delete(
            "DELETE FROM
                `" . $table . "`
            WHERE
                `" . $column . "` IN('" . implode("','", $newValues) . "')"
        );

        $data = DBHelper::fetchAll(
            "SELECT
                *
            FROM
                `" . $table . "`
            WHERE
                `" . $column . "` IN('" . implode("','", $oldValues) . "')"
        );

        foreach ($data as $entry) {
            // inject explicitly specified values
            foreach ($targetValues as $name => $value) {
                $entry[$name] = $value;
            }

            // convert the auto increment column value
            $oldValue = $entry[$column];
            $newValue = $this->autoIncrements[$sourcePath][$oldValue];
            $entry[$column] = $newValue;

            DBHelper::insertOrUpdate(
                $table,
                $entry,
                $primaryColumns
            );
        }

        $this->log(sprintf('Copied [%s] records.', count($data)));
    }

    protected function _processDataKeys(Application_RevisionableStateless $targetRevisionable): void
    {
        $revCol = $this->storage->getRevisionColumn();

        $this->copyRecords(
            $this->storage->getRevdataTable(),
            $revCol,
            array($revCol, 'data_key'),
            array($this->storage->getIDColumn() => $this->revisionable->getID()),
            array($this->storage->getIDColumn() => $targetRevisionable->getID())
        );
    }
}
