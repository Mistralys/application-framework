<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application_CustomProperties_Property;
use Application_Interfaces_Propertizable;
use Application_Traits_Propertizable;

/**
 * Stub class used to enable static analysis of the trait {@see Application_Traits_Propertizable}.
 */
final class PropertizableStub implements Application_Interfaces_Propertizable
{
    use Application_Traits_Propertizable;

    public function getPropertiesOwnerType() : string
    {
        return 'propertizable_stub';
    }

    public function getPropertiesOwnerKey() : string
    {
        return 'stub_key';
    }

    public function getPropertiesTypeNameSingular() : string
    {
        return 'Singular';
    }

    public function getPropertiesTypeNamePlural() : string
    {
        return 'Plurals';
    }

    public function isPropertiesOwnerPublishable() : bool
    {
        return true;
    }

    public function startPropertiesTransaction() : self
    {
        return $this;
    }

    public function endPropertiesTransaction() : self
    {
        return $this;
    }

    public function handle_propertyModified(Application_CustomProperties_Property $property, $partLabel, $oldValue = null, $newValue = null, $isStructural = false) : void
    {
    }

    public function handle_propertyCreated(Application_CustomProperties_Property $property) : void
    {
    }

    public function handle_propertyDeleted(Application_CustomProperties_Property $property) : void
    {
    }
}
