<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\Tags\ClientsideTagging;
use TestDriver\ClassFactory;
use UI;
use UI_Button;

class TaggingDialog
{
    private TaggableInterface $taggable;
    private ClientsideTagging $clientside;

    public function __construct(TaggableInterface $taggable)
    {
        $this->taggable = $taggable;
        $this->clientside = ClassFactory::createTags()->createClientsideTagging();
    }

    public function createButton() : UI_Button
    {
        $this->clientside->inject();

        return UI::button('')
            ->setIcon(UI::icon()->tags())
            ->setTooltip(t('Edit this item\'s tags'))
            ->click(sprintf(
                "%s.createDialog('%s', %s)",
                $this->clientside->getObjectID(),
                $this->taggable->getTagCollection()->getTagRegistryKey(),
                $this->taggable->getTagRecordPrimaryValue()
            ));
    }
}
