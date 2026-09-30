<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\Revisionables\RevisionableRecord;

/**
 * Tests for {@see \BaseRevisionable::getFormValues()}, which must merge
 * the revision data keys, the record's custom key values, and its label
 * into a single defaults array for settings forms.
 *
 * @see \BaseRevisionable::getFormValues()
 * @see RevisionableRecord
 */
final class FormValuesTest extends RevisionableTestCase
{
    public function test_includesDataKeys() : void
    {
        $revisionable = $this->createTestRevisionable();

        $revisionable->startCurrentUserTransaction();
        $revisionable->setNonStructuralDataKey('data-key-value');
        $revisionable->endTransaction();

        $values = $revisionable->getFormValues();

        $this->assertArrayHasKey(RevisionableRecord::DATA_KEY_NON_STRUCTURAL, $values);
        $this->assertSame('data-key-value', $values[RevisionableRecord::DATA_KEY_NON_STRUCTURAL]);
    }

    public function test_includesCustomKeyValues() : void
    {
        $revisionable = $this->createTestRevisionable(null, 'custom-alias-value');

        $values = $revisionable->getFormValues();

        $this->assertArrayHasKey(RevisionableCollection::COL_REV_ALIAS, $values);
        $this->assertSame('custom-alias-value', $values[RevisionableCollection::COL_REV_ALIAS]);
    }

    public function test_includesLabel() : void
    {
        $revisionable = $this->createTestRevisionable('My Test Label');

        $values = $revisionable->getFormValues();

        $this->assertArrayHasKey(RevisionableCollectionInterface::COL_REV_LABEL, $values);
        $this->assertSame('My Test Label', $values[RevisionableCollectionInterface::COL_REV_LABEL]);
    }

    /**
     * A data key sharing its name with a custom key value must lose to
     * the custom key value: {@see \BaseRevisionable::getFormValues()}
     * merges data keys first, at the lowest precedence.
     */
    public function test_customKeyValueTakesPrecedenceOverDataKey() : void
    {
        $revisionable = $this->createTestRevisionable(null, 'real-alias-value');

        // Inject a stale data key using the same name as the alias
        // custom key value, to prove it does not win the merge.
        $revisionable->setRawDataKey(RevisionableCollection::COL_REV_ALIAS, 'stale-data-key-value');

        $values = $revisionable->getFormValues();

        $this->assertSame('real-alias-value', $values[RevisionableCollection::COL_REV_ALIAS]);
    }

    /**
     * Same precedence check as above, but for the label: it must win
     * over a same-named data key too.
     */
    public function test_labelTakesPrecedenceOverDataKey() : void
    {
        $revisionable = $this->createTestRevisionable('Real Label');

        $revisionable->setRawDataKey(RevisionableCollectionInterface::COL_REV_LABEL, 'Stale Label');

        $values = $revisionable->getFormValues();

        $this->assertSame('Real Label', $values[RevisionableCollectionInterface::COL_REV_LABEL]);
    }
}
