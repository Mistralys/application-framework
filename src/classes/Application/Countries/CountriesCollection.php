<?php
/**
 * @package Application
 * @subpackage Countries
 */

declare(strict_types=1);

namespace Application\Countries;

use Application\AppFactory;
use Application_Countries;
use Application_Countries_Country;
use AppUtils\FileHelper\FolderInfo;

/**
 * Utility class for working with collections of countries,
 * with helper methods to easily access the countries.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CountriesCollection
{
    public const int ERROR_CANNOT_GET_FIRST_COUNTRY = 105201;
    public const int ERROR_CANNOT_GET_BY_ISO = 105202;
    public const int ERROR_CANNOT_GET_BY_ID = 105203;

    /**
     * @var array<int,Application_Countries_Country>
     */
    private array $countries = array();
    private Application_Countries $collection;
    private bool $excludeInvariant = false;

    protected function __construct(array $countries=array())
    {
        $this->collection = Application_Countries::getInstance();

        $this->addCountries($countries);
    }

    public static function create(array $countries=array()) : CountriesCollection
    {
        return new CountriesCollection($countries);
    }

    /**
     * @param Application_Countries_Country[] $countries
     * @return $this
     */
    public function addCountries(array $countries) : self
    {
        foreach($countries as $country)
        {
            $this->addCountry($country);
        }

        return $this;
    }

    /**
     * @param Application_Countries_Country $country
     * @return $this
     */
    public function addCountry(Application_Countries_Country $country) : self
    {
        $id = $country->getID();

        if(!isset($this->countries[$id]))
        {
            $this->countries[$id] = $country;
        }

        return $this;
    }

    public static function getAPIMethodsFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/API/Methods');
    }

    public function hasCountries() : bool
    {
        return !empty($this->countries);
    }

    public function countCountries() : int
    {
        return count($this->countries);
    }

    /**
     * @param Application_Countries_Country $country
     * @return $this
     */
    public function removeCountry(Application_Countries_Country $country) : self
    {
        $id = $country->getID();

        if(isset($this->countries[$id]))
        {
            unset($this->countries[$id]);
        }

        return $this;
    }

    /**
     * @param Application_Countries_Country[] $countries
     * @return $this
     */
    public function removeCountries(array $countries) : self
    {
        foreach($countries as $country)
        {
            $this->removeCountry($country);
        }

        return $this;
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function addIDs(array $ids) : self
    {
        foreach ($ids as $id)
        {
            $this->addID($id);
        }

        return $this;
    }

    public function addID(int $id) : self
    {
        return $this->addCountry($this->collection->getByID($id));
    }

    /**
     * @param string[] $ISOs
     * @return $this
     */
    public function addISOs(array $ISOs) : self
    {
        foreach($ISOs as $ISO)
        {
            $this->addISO($ISO);
        }

        return $this;
    }

    public function addISO(string $iso) : self
    {
        return $this->addCountry($this->collection->getByISO($iso));
    }

    /**
     * @return int[]
     */
    public function getIDs() : array
    {
        $result = array();
        $countries = $this->getAll();

        foreach ($countries as $country)
        {
            if($this->excludeInvariant === true && $country->isInvariant())
            {
                continue;
            }

            $result[] = $country->getID();
        }

        sort($result);

        return $result;
    }

    public function hasID(int $id) : bool
    {
        $exists = isset($this->countries[$id]);

        if($exists === true && $this->excludeInvariant && $this->countries[$id]->isInvariant())
        {
            return false;
        }

        return $exists;
    }

    public function hasISO(string $iso) : bool
    {
        return in_array($iso, $this->getISOs(), true);
    }

    public function getFirst() : Application_Countries_Country
    {
        $ISOs = $this->getISOs();

        if(!empty($ISOs))
        {
            return $this->getByISO(array_shift($ISOs));
        }

        throw new CountryException(
            'Cannot get first country, there no countries in the collection.',
            '',
            self::ERROR_CANNOT_GET_FIRST_COUNTRY
        );
    }

    public function getByISO(string $ISO) : Application_Countries_Country
    {
        $countries = $this->getAll();

        foreach ($countries as $country)
        {
            if($country->getISO() === $ISO)
            {
                return $country;
            }
        }

        throw new CountryException(
            'Cannot find country in collection by ISO.',
            sprintf(
                'Tried finding ISO [%s], available are [%s].',
                $ISO,
                implode(', ', $this->getISOs())
            ),
            self::ERROR_CANNOT_GET_BY_ISO
        );
    }

    public function getByID(int $id) : Application_Countries_Country
    {
        if(isset($this->countries[$id]))
        {
            return $this->countries[$id];
        }

        throw new CountryException(
            'Cannot find country in collection by ID.',
            sprintf(
                'Tried finding ID [%s], available are [%s].',
                $id,
                implode(', ', $this->getIDs())
            ),
            self::ERROR_CANNOT_GET_BY_ID
        );
    }

    /**
     * @return string[]
     */
    public function getISOs() : array
    {
        $result = array();
        $countries = $this->getAll();

        foreach ($countries as $country)
        {
            if($this->excludeInvariant === true && $country->isInvariant())
            {
                continue;
            }

            $result[] = $country->getISO();
        }

        sort($result);

        return $result;
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getAll() : array
    {
        $countries = array_values($this->countries);

        if($this->excludeInvariant === false)
        {
            return $countries;
        }

        $result = array();

        foreach ($countries as $country)
        {
            if($country->isInvariant())
            {
                continue;
            }

            $result[] = $country;
        }

        return $result;
    }

    public function excludeInvariant(bool $exclude=true) : self
    {
        $this->excludeInvariant = $exclude;
        return $this;
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getSortedByISO() : array
    {
        $countries = $this->getAll();

        usort($countries, static function(Application_Countries_Country $a, Application_Countries_Country $b) : int
        {
            return strnatcasecmp($a->getISO(), $b->getISO());
        });

        return $countries;
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getSortedByLabel() : array
    {
        $countries = $this->getAll();

        usort($countries, static function(Application_Countries_Country $a, Application_Countries_Country $b) : int
        {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $countries;
    }

    public function hasInvariant() : bool
    {
        return in_array(Application_Countries_Country::COUNTRY_INDEPENDENT_ISO, $this->getISOs());
    }

    public function hasCountry(Application_Countries_Country $country) : bool
    {
        return $this->hasID($country->getID());
    }

    /**
     * Checks whether the collection contains the specified country ID.
     * @param int $id
     * @return bool
     */
    public function idExists(int $id) : bool
    {
        return in_array($id, $this->getIDs());
    }

    /**
     * Attempts to find a country available in the collection
     * from the current request, using the standard request
     * parameter {@see Application_Countries::REQUEST_PARAM_ID}.
     * 
     * @return Application_Countries_Country|null
     */
    public function getByRequest() : ?Application_Countries_Country
    {
        $country = AppFactory::createCountries()->getByRequest();
        if($country !== null && $this->hasCountry($country)) {
            return $country;
        }

        return null;
    }
}
