<?php
/**
 * @package DBHelper
 * @subpackage Decorators
 */

declare(strict_types=1);

namespace DBHelper\Traits;

use Application_EventHandler_EventableListener;
use AppUtils\Microtime;
use DateTime;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseCollection_OperationContext_Delete;

/**
 * Trait used to implement the {@see RecordDecoratorInterface} by forwarding
 * method calls to the decorated record.
 *
 * > NOTE: Additional traits are typically used in conjunction with this trait,
 * > as demonstrated by {@see BaseRecordDecorator}.
 *
 * @package DBHelper
 * @subpackage Decorators
 *
 * @see RecordDecoratorInterface
 */
trait RecordDecoratorTrait
{
    public function getLabel(): string
    {
        return $this->getDecoratedRecord()->getLabel();
    }

    public function isStub(): bool
    {
        return $this->getDecoratedRecord()->isStub();
    }

    public function getCollection(): DBHelperCollectionInterface
    {
        return $this->getDecoratedRecord()->getCollection();
    }

    public function getRecordKey(string $name, mixed $default = null): mixed
    {
        return $this->getDecoratedRecord()->getRecordKey($name, $default);
    }

    public function getRecordIntKey(string $name, int $default = 0): int
    {
        return $this->getDecoratedRecord()->getRecordIntKey($name, $default);
    }

    public function getRecordDateKey(string $name, ?DateTime $default = null): ?DateTime
    {
        return $this->getDecoratedRecord()->getRecordDateKey($name, $default);
    }

    public function getRecordMicrotimeKey(string $name): ?Microtime
    {
        return $this->getDecoratedRecord()->getRecordMicrotimeKey($name);
    }

    public function requireRecordMicrotimeKey(string $name): Microtime
    {
        return $this->getDecoratedRecord()->requireRecordMicrotimeKey($name);
    }

    public function getRecordData(): array
    {
        return $this->getDecoratedRecord()->getRecordData();
    }

    public function getInstanceID(): string
    {
        return $this->getDecoratedRecord()->getInstanceID();
    }

    public function refreshData(): void
    {
        $this->getDecoratedRecord()->refreshData();
    }

    public function getRecordTable(): string
    {
        return $this->getDecoratedRecord()->getRecordTable();
    }

    public function getRecordPrimaryName(): string
    {
        return $this->getDecoratedRecord()->getRecordPrimaryName();
    }

    public function getRecordTypeName(): string
    {
        return $this->getDecoratedRecord()->getRecordTypeName();
    }

    public function getRecordFloatKey(string $name, float $default = 0.0): float
    {
        return $this->getDecoratedRecord()->getRecordFloatKey($name, $default);
    }

    public function getRecordStringKey(string $name, string $default = ''): string
    {
        return $this->getDecoratedRecord()->getRecordStringKey($name, $default);
    }

    public function getRecordBooleanKey(string $name, bool $default = false): bool
    {
        return $this->getDecoratedRecord()->getRecordBooleanKey($name, $default);
    }

    public function recordKeyExists(string $name): bool
    {
        return $this->getDecoratedRecord()->recordKeyExists($name);
    }

    public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno = true): bool
    {
        return $this->getDecoratedRecord()->setRecordBooleanKey($name, $boolean, $yesno);
    }

    public function setRecordDateKey(string $name, DateTime $date): bool
    {
        return $this->getDecoratedRecord()->setRecordDateKey($name, $date);
    }

    public function setRecordKey(string $name, mixed $value): bool
    {
        return $this->getDecoratedRecord()->setRecordKey($name, $value);
    }

    public function requireRecordKeyExists(string $name): bool
    {
        return $this->getDecoratedRecord()->requireRecordKeyExists($name);
    }

    public function isModified(?string $key = null): bool
    {
        return $this->getDecoratedRecord()->isModified($key);
    }

    public function hasStructuralChanges(): bool
    {
        return $this->getDecoratedRecord()->hasStructuralChanges();
    }

    public function getModifiedKeys(): array
    {
        return $this->getDecoratedRecord()->getModifiedKeys();
    }

    public function save(bool $silent = false): bool
    {
        return $this->getDecoratedRecord()->save($silent);
    }

    public function saveChained(bool $silent = false): self
    {
        $this->getDecoratedRecord()->saveChained($silent);
        return $this;
    }

    public function getParentRecord(): ?DBHelperRecordInterface
    {
        return $this->getDecoratedRecord()->getParentRecord();
    }

    public function getFormValues(): array
    {
        return $this->getDecoratedRecord()->getFormValues();
    }

    public function onKeyModified(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->getDecoratedRecord()->onKeyModified($callback);
    }

    public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void
    {
        $this->getDecoratedRecord()->onCreated($context);
    }

    public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
        $this->getDecoratedRecord()->onDeleted($context);
    }

    public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
        $this->getDecoratedRecord()->onBeforeDelete($context);
    }

    public function getChildDisposables(): array
    {
        $disposables = $this->getDecoratedRecord()->getChildDisposables();
        $disposables[] = $this->getDecoratedRecord();

        return $disposables;
    }

    protected function _dispose(): void
    {
    }

    protected function _getIdentification(): string
    {
        return $this->getDecoratedRecord()->getIdentification();
    }

    public function getID(): int
    {
        return $this->getDecoratedRecord()->getID();
    }
}
