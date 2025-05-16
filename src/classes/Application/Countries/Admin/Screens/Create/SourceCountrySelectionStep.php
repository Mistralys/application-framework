<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Create;

use AppLocalize\Localization;
use UI;

class SourceCountrySelectionStep extends BaseCreateStep
{
    public const STEP_NAME = 'SourceCountrySelection';

    public function render(): string
    {
        if(empty($this->availableCountries)) {
            return $this->renderNoCountries();
        }

        $sel = $this->ui->createBigSelection();

        foreach($this->availableCountries as $country) {
            $sel->addLink(
                $country->getLabel(),
                $this->getURL(array('iso' => $country->getID()))
            );
        }

        return $sel->render();
    }

    private function renderNoCountries() : string
    {
        return (string)sb()
            ->add($this->ui->createMessage()
                ->makeNotDismissable()
                ->makeInfo()
                ->enableIcon()
                ->setContent(sb()
                    ->bold(t('All available countries have already been added.'))
                )
            )
            ->para(UI::button(t('Cancel wizard'))
                ->setIcon(UI::icon()->back())
                ->makePrimary()
                ->link($this->getCancelURL())
            );
    }

    /**
     * @var Localization\Countries\BaseCountry[]
     */
    private array $availableCountries = array();

    protected function preProcess(): void
    {
        $existing = $this->countries->getSupportedISOs(true);

        foreach(Localization::createCountries()->getAll() as $sourceCountry) {
            if (in_array($sourceCountry->getID(), $existing, true)) {
                continue;
            }

            $this->availableCountries[] = $sourceCountry;
        }
    }

    protected function getDefaultData(): array
    {
        return array();
    }

    public function _process(): bool
    {
        return false;
    }

    public function getLabel(): string
    {
        return t('Country selection');
    }

    public function getAbstract(): string
    {
        return t('Please select one of the available countries to add.');
    }
}
