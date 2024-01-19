<?php

declare(strict_types=1);

namespace Application\Tags\Taggables\FilterCriteria;

use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use Closure;

/**
 * @see TaggableFilterCriteriaInterface
 */
trait TaggableFilterCriteriaTrait
{
    private bool $taggableInitialized = false;

    /**
     * @param TagRecord $tag
     * @return $this
     */
    public function selectTag(TagRecord $tag) : self
    {
        $this->initTaggable();

        return $this->selectCriteriaValue(TaggableFilterCriteriaInterface::FILTER_TAGGABLE_TAG_IDS, $tag->getID());
    }

    /**
     * @param TagRecord[] $tags
     * @return $this
     */
    public function selectTags(array $tags) : self
    {
        foreach ($tags as $tag) {
            $this->selectTag($tag);
        }

        return $this;
    }

    private function initTaggable() : void
    {
        if($this->taggableInitialized) {
            return;
        }

        $this->taggableInitialized = true;

        $this->onApplyFilters(
            Closure::fromCallable(array($this, 'configureTaggableCriteria'))
        );
    }

    private function configureTaggableCriteria(): void
    {
        $tagIDs = $this->getCriteriaValues(self::FILTER_TAGGABLE_TAG_IDS);
        if(empty($tagIDs)) {
            return;
        }

        $container = $this->getTagContainer();

        $values = statementValues()
            ->table('{table_connection}', $container->getTagTable())
            ->table('{table_source}', $container->getTagSourceTable())
            ->field('{tag_primary}', TagCollection::PRIMARY_NAME)
            ->field('{record_primary}', $container->getTagPrimary());

        $join = <<<'EOT'
LEFT JOIN
    {table_connection}
ON
    {table_source}.{record_primary} = {table_connection}.{record_primary}
EOT;

        $this->addJoin(statementBuilder($join, $values));

        $this->addWhereColumnIN(
            statementBuilder(
                '{table_connection}.{tag_primary}',
                $values
            ),
            $tagIDs
        );
    }
}
