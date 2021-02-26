<?php

final class User_RightsTest extends UserTestCase
{
    public function test_registerGroup() : void
    {
        $rights = new Application_User_Rights();

        $group = $rights->registerGroup('foo', 'Foo');

        $this->assertEquals('foo', $group->getID());
        $this->assertEquals('Foo', $group->getLabel());
        $this->assertEmpty($group->getRights()->getAll());
    }

    public function test_registerRight() : void
    {
        $rights = new Application_User_Rights();

        $group = $rights->registerGroup('foo', 'Foo');

        $right = $group->registerRight('FooRight', 'Foo right');

        $this->assertEquals('FooRight', $right->getID());
        $this->assertEquals('Foo right', $right->getLabel());
        $this->assertEmpty($right->getDescription());

        $right->setDescription('Descr');

        $this->assertEquals('Descr', $right->getDescription());
    }

    public function test_groupRights() : void
    {
        $rights = new Application_User_Rights();

        $group = $rights->registerGroup('foo', 'Foo');
        $group->registerRight('Right1', 'Right 1');
        $group->registerRight('Right2', 'Right 2');

        $groupRights = $group->getRights()->getAll();

        $this->assertSame(2, count($groupRights));
    }

    /**
     * "ViewCategory" grants "ViewProduct", which in turn grants "ViewProperties",
     * which grants "ViewPropertyType".
     */
    public function test_grant() : void
    {
        $rights = $this->createRightsStructure();

        $right = $rights->getRightByID('ViewCategory');

        $grants = $right->getGrants()->getIDs();

        $this->assertEquals(array('ViewProduct'), $grants);

        $grants = $right->resolveGrants()->getIDs();

        $this->assertEquals(array('ViewProduct', 'ViewProperties', 'ViewPropertyType'), $grants);
    }

    public function test_grantEdit() : void
    {
        $rights = $this->createRightsStructure();

        $right = $rights->getRightByID('EditProduct');

        $grants = $right->resolveGrants()->getIDs();

        $expected = array(
            'CreatePropertyType',
            'DeletePropertyType',
            'EditProperties',
            'EditPropertyType',
            'ViewProduct',
            'ViewProperties',
            'ViewPropertyType'
        );

        $this->assertEquals($expected, $grants);
    }

    /**
     * Some rights may grant rights that grant the original right
     * in return. This must not break the recursion, but the granted
     * rights must still be fully populated.
     */
    public function test_grantCyclic() : void
    {
        $rights = new Application_User_Rights();

        $cyclic = $rights->registerGroup('Cyclic', 'Cyclic rights');
        $cyclic->registerRight('CyclicSource', 'Source')
            ->grantRight('CyclicTarget')
            ->grantRight('CyclicExtension');

        $cyclic->registerRight('CyclicExtension', '');

        $target = $rights->registerGroup('Target', 'Cyclic target');
        $target->registerRight('CyclicTarget', 'target')
            ->grantRight('CyclicSource');

        $right = $rights->getRightByID('CyclicSource');

        $grants = $right->resolveGrants()->getIDs();

        // The "CyclicSource" right must not be present, but the
        // "CyclicExtension" that it grants must be present.
        $this->assertEquals(array('CyclicExtension', 'CyclicTarget'), $grants);
    }

    private function createRightsStructure() : Application_User_Rights
    {
        $rights = new Application_User_Rights();

        $products = $rights->registerGroup('Products', 'Products');
        $products->registerRight('ViewProduct', 'Viewing')
            ->actionView()
            ->grantRight('ViewProperties');

        $products->registerRight('EditProduct', 'Editing')
            ->actionEdit()
            ->grantRight('ViewProduct')
            ->grantRight('EditProperties');

        $products->registerRight('CreateProduct', 'Creating')
            ->actionCreate()
            ->grantRight('EditProduct');

        $products->registerRight('DeleteProduct', 'Deleting')
            ->actionDelete()
            ->grantRight('EditProduct')
            ->grantRight('CreateProduct');

        // PROPERTIES ------------------------------------------------------------
        // Properties grant access to the property types.

        $properties = $rights->registerGroup('Properties', 'Properties');
        $properties->registerRight('ViewProperties', 'Viewing')
            ->actionView()
            ->grantGroupView('PropertyTypes');

        $properties->registerRight('EditProperties', 'Edit properties')
            ->actionEdit()
            ->grantRight('ViewProperties')
            ->grantGroupAll('PropertyTypes');

        // CATEGORIES ------------------------------------------------------------

        $categories = $rights->registerGroup('Categories', 'Product categories');
        $categories->registerRight('ViewCategory', 'Viewing')
            ->actionView()
            ->grantGroupView('Products');

        // PROPERTY TYPES ------------------------------------------------------------

        $properties = $rights->registerGroup('PropertyTypes', 'Property types');

        $properties->registerRight('ViewPropertyType', 'Viewing')
            ->actionView();

        $properties->registerRight('EditPropertyType', 'Editing')
            ->actionEdit()
            ->grantRight('ViewPropertyType');

        $properties->registerRight('CreatePropertyType', 'Creating')
            ->actionCreate()
            ->grantRight('EditPropertyType');

        $properties->registerRight('DeletePropertyType', 'Deleting')
            ->actionDelete()
            ->grantRight('EditPropertyType');

        return $rights;
    }
}
