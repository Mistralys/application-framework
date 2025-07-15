<?php
/**
 * @package LDAP
 * @see Application_LDAP
 */

declare(strict_types=1);

use function AppUtils\parseVariable;

/**
 * LDAP utility class used to communicate with an LDAP server
 * to access user rights.
 *
 * ## Prerequisites
 *
 * The implementation expects user rights to be handled by
 * assigning rights to roles, which are then assigned to
 * users. Fetching rights for a user will then return all
 * rights that are assigned to the roles that the user
 * is a member of.
 *
 * The rights are expected to be stored in a custom attribute
 * called {@see self::ATTRIBUTE_RIGHT_NAME}. To facilitate searches,
 * roles must also have a custom attribute called {@see self::ATTRIBUTE_RIGHT_TYPE_ID},
 * which is used mainly to filter out roles that do not contain rights.
 *
 * @package LDAP
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_LDAP implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_CONNECT_FAILED = 72001;
    public const ERROR_BINDING_FAILED = 72002;

    private const CONNECTION_CLASS = 'LDAP\Connection';

    public const ATTRIBUTE_RIGHT_NAME = 'rightname';
    public const ATTRIBUTE_RIGHT_TYPE_ID = 'righttypeid';

    private Application_LDAP_Config $config;

    /**
     * @var resource|LDAP\Connection
     */
    protected $connection;

    private bool $debug = false;

    public function __construct(Application_LDAP_Config $config)
    {
        $this->config = $config;

        $this->setDebug($config->isDebug());

        $this->log('Starting new connection.');

        $this->configureConnection();
    }

    private function configureConnection(bool $fallback=false) : void
    {
        $this->log('ConfigureConnection | Setting up the connection configuration.');

        // Workaround for LDAPS issues
        putenv("LDAPTLS_REQCERT=never");

        if(PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 4)
        {
            $this->log('ConfigureConnection | Using PHP 8.4+ connection using URI [%s].', $this->config->getURI());
            $result = ldap_connect($this->config->getURI());
        }
        else
        {
            $result = $this->connectPHP7($fallback);
        }

        if (!is_resource($result) && !class_exists(self::CONNECTION_CLASS) && !is_a($result, self::CONNECTION_CLASS)) {
            throw new Application_Exception(
                'LDAP connection configuration failed',
                sprintf(
                    'LDAP `connect` function call failed on [%1s:%2s]. '.PHP_EOL.
                    'This does not mean the actual connection failed, just the configuration. '.PHP_EOL.
                    'Connect return type: [%3$s]. ',
                    $this->config->getHost(),
                    $this->config->getPort(),
                    parseVariable($result)->enableType()->toString()
                ),
                self::ERROR_CONNECT_FAILED
            );
        }

        $this->connection = $result;

        ldap_set_option(
            $this->connection,
            LDAP_OPT_PROTOCOL_VERSION,
            $this->config->getProtocolVersion()
        );

        $this->log(sprintf(
            'ConfigureConnection | OK | User [%s] | Base DN [%s]',
            $this->config->getUsername(),
            $this->config->getDn()
        ));
    }

    /**
     * @param bool $fallback Whether to use the fallback host configuration.
     * @return false|resource
     */
    private function connectPHP7(bool $fallback = false)
    {
        $host = $this->config->getHostURI();
        $port = $this->config->getPort();

        // Depending on the LDAP + PHP combination, the host may
        // have to be specified without the scheme.
        if($fallback) {
            $host = $this->config->getHost();
        }

        $this->log(
            'ConfigureConnection | Using PHP 7.x connection using host [%s] and port [%s].',
            $host,
            $port
        );

        return @ldap_connect(
            $host,
            $port
        );
    }

    public function getConfig() : Application_LDAP_Config
    {
        return $this->config;
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function setDebug(bool $debug) : self
    {
        $this->debug = $debug;
        return $this;
    }

    private bool $isBound = false;

    private function _bind() : bool
    {
        return @ldap_bind(
            $this->connection,
            $this->config->getUsername(),
            $this->config->getPassword()
        );
    }

    /**
     * Starts the actual connection to the LDAP server.
     *
     * @throws Application_Exception
     */
    private function bind() : void
    {
        if ($this->isBound) {
            return;
        }

        $this->log('Binding | Connecting to the LDAP server.');

        $this->isBound = true;

        if ($this->debug) {
            $this->log('Binding | Debugging enabled, setting LDAP debug level to 7.');
            // Setting the option only works without specifying the connection resource
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        if($this->_bind() !== false)
        {
            $this->log('Binding | OK, connection established.');
            return;
        }

        $this->log('Binding | Connection failed, trying fallback method.');

        // Retry with the fallback connection configuration.
        $this->configureConnection(true);

        if($this->_bind() !== false)
        {
            $this->log('Binding | OK, connection established.');
            return;
        }

        $message = ldap_error($this->connection);

        $this->logError('Binding | Connection failed with message [%s].', $message);

        throw new Application_Exception(
            'LDAP binding failed',
            sprintf(
                'Could not bind ldap on [%1$s] (the connection failed). Native error message: %2$s',
                $this->config->getURI(),
                $message
            ),
            self::ERROR_BINDING_FAILED
        );
    }

    /**
     * Retrieves a flat list of right names for the specified username.
     *
     * @param string $userName The user name, e.g. "smordziol".
     * @return string[]
     */
    public function getRights(string $userName) : array
    {
        $this->bind();

        $this->log(sprintf('User [%s] | Fetching rights.', $userName));

        $roles = $this->getRoleDNs($userName);
        $rights = array();

        foreach ($roles as $roleDN)
        {
            array_push($rights, ...$this->getRoleRights($roleDN));
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
        $this->bind();

        $this->log(sprintf('FetchRights | Fetching rights for role [%s].', $roleDN));

        // Fetch all right entries that match the right ID to
        // access the name of the right.
        $rightDefs = $this->search("(".self::ATTRIBUTE_RIGHT_TYPE_ID."=*)", array(), $roleDN);
        $rights = array();

        foreach ($rightDefs as $rightDef)
        {
            // Fallback check: Verify that the DN in the result set matches
            // the role DN.
            $dn = $rightDef['dn'] ?? '';
            if(!empty($dn) && !str_starts_with($dn, $roleDN))
            {
                $this->log(
                    'FetchRights | Skipping role [%s], as it does not match the role DN.',
                    $dn,
                );
                continue;
            }

            foreach ($rightDef[self::ATTRIBUTE_RIGHT_NAME] as $key => $value)
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
        $this->bind();

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
     * @param string $userName
     * @return string
     */
    private function getRolesFilter(string $userName) : string
    {
        // By default, members are matched by the value of the
        // "member" property, which can look like this:
        //
        // `uid=smordziol`
        //
        // Depending on how rights are set up, however, the uid
        // can be a lot more verbose, like this:
        //
        // `uid=smordziol,ou=category,o=Organization,c=DE`
        //
        // In this case, the configuration allows specifying
        // the suffix string to add after the uid search, as
        // a wildcard search like `uid=smordziol*` will not work.
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
        $this->bind();

        if(empty($baseDn)) {
            $baseDn = $this->config->getDn();
            $this->log('Search | No base DN specified, using the default [%s].', $baseDn);
        } else {
            $this->log('Search | Using custom base DN [%s].', $baseDn);
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
