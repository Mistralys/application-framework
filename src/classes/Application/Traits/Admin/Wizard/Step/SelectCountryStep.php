<?php
/**
 * File containing the trait {@see Application_Traits_Admin_Wizard_SelectCountryStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Traits_Admin_Wizard_SelectCountryStep
 */

declare(strict_types=1);

/**
 * Step in an admin wizard: select a country.
 *
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Stubs_Admin_Wizard_SelectCountryStub
 * @see Application_Interfaces_Admin_Wizard_SelectCountryStep
 */
trait Application_Traits_Admin_Wizard_SelectCountryStep
{
    public function getLabel()
    {
        return t('Country');
    }

    public function getIcon() : ?UI_Icon
    {
        return UI::icon()->countries();
    }

    abstract public function getAbstract() : string;

    abstract public function isInvariantSelectable() : bool;

    public function getCollection() : Application_Countries
    {
        return Application_Countries::getInstance();
    }

    public function getCountry() : ?Application_Countries_Country
    {
        $collection = $this->getCollection();

        $id = intval($this->getDataKey(Application_Interfaces_Admin_Wizard_SelectCountryStep::PARAM_COUNTRY_ID));

        if(!empty($id) && $collection->idExists($id))
        {
            return $collection->getCountryByID($id);
        }

        return null;
    }

    /**
     * @return Application_Countries_Country
     *
     * @throws Application_Admin_WizardException
     * @see Application_Interfaces_Admin_Wizard_SelectCountryStep::ERROR_NO_COUNTRY_SELECTED
     */
    public function requireCountry() : Application_Countries_Country
    {
        $country = $this->getCountry();

        if($country !== null)
        {
            return $country;
        }

        throw new Application_Admin_WizardException(
            'No country selected.',
            'The country cannot be retrieved, none has been selected and stored yet.',
            Application_Interfaces_Admin_Wizard_SelectCountryStep::ERROR_NO_COUNTRY_SELECTED
        );
    }

    protected function getDefaultData()
    {
        return array(
            Application_Interfaces_Admin_Wizard_SelectCountryStep::PARAM_COUNTRY_ID => null
        );
    }

    public function _process()
    {
        $country = $this->getCollection()->getByRequest();

        if(!$country)
        {
            return false;
        }

        $this->setData(Application_Interfaces_Admin_Wizard_SelectCountryStep::PARAM_COUNTRY_ID, $country->getID());

        return true;
    }

    public function render()
    {
        $countries = $this->getCollection()->getAll($this->isInvariantSelectable());

        $sel = $this->ui->createBigSelection();

        foreach($countries as $country)
        {
            $url = $this->getURL(array(
                Application_Interfaces_Admin_Wizard_SelectCountryStep::PARAM_COUNTRY_ID => (string)$country->getID()
            ));

            $sel->addLink(
                $country->getIconLabel(),
                $url
            );
        }

        return (string)$sel;
    }
}
