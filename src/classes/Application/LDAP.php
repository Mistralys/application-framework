<?php
/**
 * File containing the {@see Application_LDAP} class.
 *
 * @package Application
 * @subpackage LDAP
 * @see Application_LDAP
 */

declare(strict_types=1);

/**
 * @package Application
 * @subackage LDAP
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_LDAP implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_CONNECT_FAILED = 72001;
    public const ERROR_BINDING_FAILED = 72002;

    /**
     * @var Application_LDAP_Config
     */
    private $config;

    /**
     * @var resource|NULL
     */
    protected $connection = null;

    public function __construct(Application_LDAP_Config $config)
    {
        $this->config = $config;

        $this->log('Starting new connection.');

        // Workaround for LDAPS issues
        putenv("LDAPTLS_REQCERT=never");

        $result = ldap_connect(
            APP_LDAP_HOST,
            APP_LDAP_PORT
        );

        if (!is_resource($result)) {
            throw new Application_Exception(
                'LDAP connection failed',
                sprintf(
                    'Connect call failed on [%1s:%2s]. This does not mean the actual connection failed, just the configuration.',
                    $this->config->getHost(),
                    $this->config->getPort()
                ),
                self::ERROR_CONNECT_FAILED
            );
        }

        $this->connection = $result;

        $this->log(sprintf(
            'Configuration OK | User [%s] | Base DN [%s]',
            $this->config->getUsername(),
            $this->config->getDn()
        ));

        $this->bind();
    }

    /**
     * Starts the actual connection to the LDAP server.
     *
     * @throws Application_Exception
     */
    private function bind() : void
    {
        $result = ldap_bind(
            $this->connection,
            $this->config->getUsername(),
            $this->config->getPassword()
        );

        if($result !== false)
        {
            $this->log('Binding was successful.');
            return;
        }

        $message = ldap_error($this->connection);

        throw new Application_Exception(
            'LDAP binding failed',
            sprintf(
                'Could not bind ldap on [%1$s:%2$s] (the connection failed). Native error message: %3$s',
                $this->config->getHost(),
                $this->config->getPort(),
                $message
            ),
            self::ERROR_BINDING_FAILED
        );
    }

    /**
     * Retrieves a list of the names of all rights for the specified user name.
     *
     * @param string $userName The user name, e.g. "smordziol".
     * @return string[]
     */
    public function getRights(string $userName) : array
    {
        $this->log(sprintf('User [%s] | Fetching rights.', $userName));

        $roles = $this->getRoleDNs($userName);
        $rights = array();

        foreach ($roles as $roleDN)
        {
            $rights = array_merge($rights, $this->getRoleRights($roleDN));
        }

		$rights = array_unique($rights);

		sort($rights);

        $this->log(sprintf(
            'User [%s] | Found [%s] rights: [%s].',
            $userName,
            count($rights),
            implode(', ', $rights)
        ));
		
        return $rights;
    }

    /**
     * Gets all rights available for the specified role DN.
     *
     * @param string $roleDN
     * @return array
     */
    public function getRoleRights(string $roleDN) : array
    {
        // Fetch all right entries that match the right ID to
        // access the name of the right.
        $rightDefs = $this->search("(righttypeid=*)", array(), $roleDN);
        $rights = array();

        foreach ($rightDefs as $rightDef)
        {
            foreach ($rightDef['rightname'] as $key => $value)
            {
                if ($key === 'count')
                {
                    continue;
                }

                $rights[] = $value;
            }
        }

        return $rights;
    }

    /**
     * Retrieves the DNs for all roles available for the user.
     *
     * @param string $userName
     * @return string[]
     */
    public function getRoleDNs(string $userName) : array
    {
        $rolesFilter = $this->getRolesFilter($userName);

        // Restrict the search by the "member" property value, to find all
        // roles that have this user in their members list.
        $roleDefs = $this->search($rolesFilter, array());

        $this->log(sprintf(
            'User [%s] | Found [%s] roles using member filter [%s].',
            $userName,
            count($roleDefs),
            $rolesFilter
        ));

        $result = array();
        foreach ($roleDefs as $roleDef)
        {
            $result[] = $roleDef['dn'];
        }

        return $result;
    }

    /**
     * Determines the search filter string to use to fetch all
     * roles that the user is a member of.
     *
     * @return string
     */
    private function getRolesFilter(string $userName) : string
    {
        // By default, members are matched by the value of the
        // "member" property, which can look like this:
        //
        // uid=smordziol
        //
        // Depending on how rights are set up however, the uid
        // can be a lot more verbose, like this:
        //
        // uid=smordziol,ou=category,o=Organization,c=DE
        //
        // In this case, the configuration allows specifying
        // the suffix string to add after the uid search, as
        // a wildcard search like "uid=smordziol*" will not work.

        $memberSearch = sprintf(
            'uid=%s%s',
            $userName,
            $this->config->getMemberSuffix()
        );

        return sprintf("(member=%s)", $memberSearch);
    }

    /**
     * @param string $filter
     * @param string[] $attributes
     * @param string $baseDn
     * @return array<string,mixed>
     */
    private function search(string $filter, array $attributes, string $baseDn = '') : array
    {
        if(empty($baseDn))
        {
            $baseDn = $this->config->getDn();
        }

        $result = ldap_search($this->connection, $baseDn, $filter, $attributes);

        if ($result === false)
        {
            return array();
        }

        $entries = ldap_get_entries($this->connection, $result);
        if ($entries === false)
        {
            return array();
        }

        if (isset($entries["count"]))
        {
            unset($entries["count"]);
        }

        return $entries;
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'LDAP [%s:%s]',
            $this->config->getHost(),
            $this->config->getPort()
        );
    }
}
