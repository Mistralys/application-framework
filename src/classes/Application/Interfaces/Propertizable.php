<?php

interface Application_Interfaces_Propertizable
{
    /**
     * Creates the properties collection used to manage properties for
     * this item's owner type.
     *
     * @return Application_CustomProperties
     */
    public function getProperties();
    
    /**
     * Retrieves a custom property by its ID.
     * @param integer $property_id
     * @return Application_CustomProperties_Property|NULL
     */
    public function getPropertyByID($property_id);
    
    /**
     * Retrieves a string identifying the type of owner of the
     * properties, e.g. "Product". Note: this is used in method
     * names.
     *
     * WARNING: This should not be changed once live, otherwise
     * all existing records will be orphaned.
     *
     * @return string Max. 250 characters, should not contain special characters.
     */
    public function getPropertiesOwnerType();
    
    /**
     * Retrieves a string namespacing the properties within the
     * type of owner. For example the ID of the product.
     *
     * @return string Max. 250 characters.
     */
    public function getPropertiesOwnerKey();
    
    public function getPropertiesTypeNameSingular();
    
    public function getPropertiesTypeNamePlural();
    
    /**
     * Whether the owner supports publishing: this enables
     * the "Is structural?" option in the properties, and the
     * functionality to tell the owner of changes to properties.
     *
     * @return boolean
     */
    public function isPropertiesOwnerPublishable();
    
    /**
     * Starts a transaction during which properties may be modified.
     * This should return the instance of the propertizable item that
     * will be used to access the properties to modify.
     *
     * In most cases, this will be the same instance. In case of
     * revisionables, this may be a transaction instance.
     *
     * @return $this
     */
    public function startPropertiesTransaction();
    
    /**
     * See the {@link startPropertiesTransaction()} method: this
     * should also return the relevant instance of the propertizable
     * item.
     *
     * @return $this
     */
    public function endPropertiesTransaction();
    
    /**
     * Called when a property's data has been modified.
     * NOTE: Only used whe the owner is publishable.
     *
     * @param Application_CustomProperties_Property $property
     */
    public function handle_propertyModified(Application_CustomProperties_Property $property, $partLabel, $oldValue=null, $newValue=null, $isStructural=false);
    
    /**
     * Called when a new property has been added to the object.
     *
     * @param Application_CustomProperties_Property $property
     */
    public function handle_propertyCreated(Application_CustomProperties_Property $property);
    
    /**
     * Called when a property has been deleted from the object.
     *
     * @param Application_CustomProperties_Property $property
     */
    public function handle_propertyDeleted(Application_CustomProperties_Property $property);
}
