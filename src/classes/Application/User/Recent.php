<?php
/**
 * File containing the class {@Application_User_Recent}.
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Recent
 */

declare(strict_types=1);

/**
 * Recent items manager, used to store and access recent items
 * that the user worked on, to display them in the welcome screen.
 *
 * This must be extended by the driver to add the driver-specific
 * setup of categories and items.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_User_Recent implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    const ERROR_CATEGORY_ALIAS_NOT_FOUND = 72701;
    const ERROR_CATEGORY_ALIAS_EXISTS = 72702;

    /**
     * @var Application_User
     */
    private $user;

    /**
     * @var Application_User_Recent_Category[]
     */
    private $categories = array();

    public function __construct(Application_User $user)
    {
        $this->user = $user;

        $this->registerCategories();

        $this->log(sprintf('Registered [%s] categories.', count($this->categories)));

        uasort($this->categories, function (Application_User_Recent_Category $a, Application_User_Recent_Category $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
    }

    abstract protected function registerCategories() : void;

    protected function registerCategory(string $alias, string $label) : Application_User_Recent_Category
    {
        if($this->categoryAliasExists($alias))
        {
            throw new Application_Exception(
                'Recent items c1ategory alias already exists.',
                sprintf(
                    'Cannot add category with alias [%s], a category has already been added with the same alias.',
                    $alias
                ),
                self::ERROR_CATEGORY_ALIAS_EXISTS
            );
        }

        $this->log(sprintf('Register category [%s].', $alias));

        $category = new Application_User_Recent_Category($this, $alias, $label);

        $this->categories[] = $category;

        return $category;
    }

    public function categoryAliasExists(string $alias) : bool
    {
        $aliases = $this->getCategoryAliases();

        return in_array($alias, $aliases);
    }

    /**
     * @param string $alias
     * @return Application_User_Recent_Category
     * @throws Application_Exception
     *
     * @see Application_User_Recent::ERROR_CATEGORY_ALIAS_NOT_FOUND
     */
    public function getCategoryByAlias(string $alias) : Application_User_Recent_Category
    {
        foreach ($this->categories as $category)
        {
            if($category->getAlias() === $alias)
            {
                return $category;
            }
        }

        throw new Application_Exception(
            'Recent items category not found.',
            sprintf(
                'The category with alias [%s] could not be found. Registered categories are: [%s].',
                $alias,
                implode(', ', $this->getCategoryAliases())
            ),
            self::ERROR_CATEGORY_ALIAS_NOT_FOUND
        );
    }

    /**
     * @return string[]
     */
    public function getCategoryAliases() : array
    {
        $result = array();

        foreach ($this->categories as $category)
        {
            $result[] = $category->getAlias();
        }

        return $result;
    }

    /**
     * @return Application_User
     */
    public function getUser(): Application_User
    {
        return $this->user;
    }

    /**
     * @return Application_User_Recent_Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function hasEntries() : bool
    {
        foreach ($this->categories as $category)
        {
            if($category->hasEntries())
            {
                return true;
            }
        }

        return false;
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'User [%s] | Recent items',
            $this->user->getID()
        );
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Welcome::URL_NAME_WELCOME;

        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }
}
