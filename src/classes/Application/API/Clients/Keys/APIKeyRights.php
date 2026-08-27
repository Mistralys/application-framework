<?php
/**
 * @package API Clients
 * @subpackage API Keys
 */

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use Application\API\APIManager;
use Application\Application;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;

/**
 * Answers method-scoped rights questions for an API key
 * from the key's method grants with one-level grant expansion.
 *
 * The key — not the pseudo user — is the authority. Each method
 * declares its right; the key can answer directly without any
 * rights-bearing user object.
 *
 * @package API Clients
 * @subpackage API Keys
 */
final class APIKeyRights implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private APIKeyRecord $key;

    public function __construct(APIKeyRecord $key)
    {
        $this->key = $key;
    }

    public function getLogIdentifier(): string
    {
        return sprintf('APIKeyRights | Key #%s', $this->key->getID());
    }

    /**
     * Whether this key satisfies the given right for the given method.
     *
     * Algorithm:
     * 1. Method not granted → false.
     * 2. Read declared right from the method index entry.
     * 3. Declared right null → false (no authority to confer).
     * 4. Declared right === requested right → true.
     * 5. One-level grant expansion via Right::hasGrant().
     *
     * An unregistered declared right is logged and treated as a denial.
     */
    public function satisfies(string $methodName, string $rightID) : bool
    {
        if(!$this->key->getMethods()->hasMethod($methodName)) {
            return false;
        }

        $declaredRight = APIManager::getInstance()
            ->getMethodIndex()
            ->getEntry($methodName)
            ->getRequiredRight();

        if($declaredRight === null) {
            return false;
        }

        if($declaredRight === $rightID) {
            return true;
        }

        $rightsManager = Application::createSystemUser()->getRightsManager();

        if(!$rightsManager->rightIDExists($declaredRight)) {
            $this->log(
                'Unresolvable declared right denial: key [%s], method [%s], declared [%s], requested [%s].',
                $this->key->getID(),
                $methodName,
                $declaredRight,
                $rightID
            );
            return false;
        }

        return $rightsManager->getRightByID($declaredRight)->hasGrant($rightID);
    }
}
