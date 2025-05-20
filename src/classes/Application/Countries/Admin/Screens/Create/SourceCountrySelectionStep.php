<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Create;

use Application\Countries\CountryException;
use AppLocalize\Localization;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use UI;

class SourceCountrySelectionStep extends BaseCreateStep
{
    public const STEP_NAME = 'SourceCountrySelection';
    public const REQUEST_PARAM_ISO = 'iso';
    public const DATA_KEY_ISO = 'iso';

    public function getID(): string
    {
        return self::STEP_NAME;
    }

    public function render(): string
    {
        if(empty($this->availableCountries)) {
            return $this->renderNoCountries();
        }

        $sel = $this->ui->createBigSelection();

        foreach($this->availableCountries as $country) {
            $sel->addLink(
                $country->getLabel(),
                $this->getURL(array(self::REQUEST_PARAM_ISO => $country->getID()))
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
     * @var CountryInterface[]
     */
    private array $availableCountries = array();
    private CountryCollection $localizationCountries;

    protected function init(): void
    {
        parent::init();

        $this->localizationCountries = Localization::createCountries();
    }

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
        return array(
            self::DATA_KEY_ISO => null
        );
    }

    public function _process(): bool
    {
        $iso = $this->request->getParam(self::REQUEST_PARAM_ISO);

        if(is_string($iso) && !empty($iso) && $this->localizationCountries->isoExists($iso)) {
            $this->setData(self::DATA_KEY_ISO, $iso);
            $this->setComplete();
            return true;
        }

        return false;
    }

    public function getCountry() : ?CountryInterface
    {
        $iso = $this->getDataKey(self::DATA_KEY_ISO);

        if(!empty($iso)) {
            return $this->localizationCountries->getByISO($iso);
        }

        return null;
    }

    public function requireCountry() : CountryInterface
    {
        $country = $this->getCountry();

        if($country !== null) {
            return $country;
        }

        throw new CreateWizardException(
            'No country has been selected.',
            'No ISO was stored in the current data set.',
            CreateWizardException::ERROR_NO_COUNTRY_SELECTED
        );
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
