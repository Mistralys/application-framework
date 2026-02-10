<?php
/**
 * @package TestDriver
 * @supackage Collection
 */

declare(strict_types=1);

namespace TestDriver\Collection;

use Application\Collection\StringCollectionInterface;
use Application\Collection\StringCollectionItemInterface;
use Application\Disposables\DisposableTrait;
use Application\EventHandler\Eventables\EventableTrait;
use Application_Traits_Loggable;

/**
 * Collection that implements the interface {@see StringCollectionInterface},
 * used to showcase the usage of the interface in a test environment.
 *
 * @package TestDriver
 * @subpackage Collection
 */
class MythologyRecordCollection implements StringCollectionInterface
{
    use DisposableTrait;
    use Application_Traits_Loggable;
    use EventableTrait;

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

    public function getFilterCriteria() : MythologyFilterCriteria
    {
        return new MythologyFilterCriteria();
    }

    public function getChildDisposables(): array
    {
        return array();
    }

    public function getRandom() : MythologicalRecord
    {
        return $this->getByID(array_rand($this->items));
    }

    protected function _dispose(): void
    {
    }

    protected function _getIdentification(): string
    {
        return 'Custom Record Collection';
    }

    public function idExists(string $record_id): bool
    {
        return isset($this->items[$record_id]);
    }

    public function getByID(string $record_id): MythologicalRecord
    {
        return $this->items[$record_id];
    }

    /**
     * @return MythologicalRecord[]
     */
    public function getAll() : array
    {
        return array_values($this->items);
    }

    public function createStubRecord() : MythologicalRecord
    {
        return new MythologicalRecord(self::RECORD_STUB, 'Stub');
    }
}
