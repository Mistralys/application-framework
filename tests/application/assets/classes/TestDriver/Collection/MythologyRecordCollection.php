<?php
/**
 * @package TestDriver
 * @supackage Collection
 */

declare(strict_types=1);

namespace TestDriver\Collection;

use Application\Collection\StringCollectionInterface;
use Application\Collection\StringCollectionItemInterface;
use Application_Traits_Disposable;
use Application_Traits_Eventable;
use Application_Traits_Loggable;

/**
 * Collection that implements the interface {@see StringCollectionInterface},
 * used to showcase the usage of the interface in a test environment.
 *
 * @package TestDriver
 * @supackage Collection
 */
class MythologyRecordCollection implements StringCollectionInterface
{
    use Application_Traits_Disposable;
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;

    public const RECORD_ATHENA = 'Athena';
    public const RECORD_ZEUS = 'Zeus';
    public const RECORD_ARES = 'Ares';
    public const RECORD_HADES = 'Hades';
    public const RECORD_POSEIDON = 'Poseidon';
    public const RECORD_STUB = 'Stub';

    public const REQUEST_VAR_NAME = 'custom_record_id';

    private array $items = array();

    private static ?MythologyRecordCollection $instance = null;

    public static function getInstance() : MythologyRecordCollection
    {
        if(self::$instance === null) {
            self::$instance = new MythologyRecordCollection();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->addItem(self::RECORD_ARES, 'Ares');
        $this->addItem(self::RECORD_ATHENA, 'Athena');
        $this->addItem(self::RECORD_HADES, 'Hades');
        $this->addItem(self::RECORD_POSEIDON, 'Poseidon');
        $this->addItem(self::RECORD_ZEUS, 'Zeus');
    }

    private function addItem(string $id, string $label) : void
    {
        $this->items[$id] = new MythologicalRecord($id, $label);
    }

    public function getFilterCriteria()
    {

    }

    public function getChildDisposables(): array
    {
        return array();
    }

    protected function _dispose(): void
    {
    }

    protected function _getIdentification(): string
    {
        return 'Custom Record Collection';
    }

    public function idExists($record_id): bool
    {
        $record_id = (string)$record_id;

        return isset($this->items[$record_id]);
    }

    public function getByID($record_id): StringCollectionItemInterface
    {
        return $this->items[(string)$record_id];
    }

    public function getAll() : array
    {
        return array_values($this->items);
    }

    public function createDummyRecord() : MythologicalRecord
    {
        return new MythologicalRecord(self::RECORD_STUB, 'Stub');
    }
}
