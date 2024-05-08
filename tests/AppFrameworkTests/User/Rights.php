<?php

use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

final class User_RightsTest extends UserTestCase
{
    public function test_registerGroup() : void
    {
        $rights = new Application_User_Rights();

        $group = $rights->registerGroup('foo', 'Foo', function() {});

        $this->assertEquals('foo', $group->getID());
        $this->assertEquals('Foo', $group->getLabel());
        $this->assertEmpty($group->getRights()->getAll());
    }

    public function test_registerRight() : void
    {
        $rights = new Application_User_Rights();

        $group = $rights->registerGroup('foo', 'Foo', function(Application_User_Rights_Group $group) {
            $group->registerRight('FooRight', 'Foo right');
        });

        $right = $group->getRights()->getByID('FooRight');

        $this->assertEquals('FooRight', $right->getID());
        $this->assertEquals('Foo right', $right->getLabel());
        $this->assertEmpty($right->getDescription());

        $right->setDescription('Descr');

        $this->assertEquals('Descr', $right->getDescription());
    }

    public function test_groupRights() : void
    {
        $rights = new Application_User_Rights();

        $group = $rights->registerGroup('foo', 'Foo', function(Application_User_Rights_Group $group) {
            $group->registerRight('Right1', 'Right 1');
            $group->registerRight('Right2', 'Right 2');
        });

        $groupRights = $group->getRights()->getAll();

        $this->assertCount(2, $groupRights);
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

        $rights->registerGroup('Cyclic', 'Cyclic rights', function(Application_User_Rights_Group $group) {
            $group->registerRight('CyclicSource', 'Source')
                ->grantRight('CyclicTarget')
                ->grantRight('CyclicExtension');

            $group->registerRight('CyclicExtension', '');
        });

        $rights->registerGroup('Target', 'Cyclic target', function(Application_User_Rights_Group $group) {
            $group->registerRight('CyclicTarget', 'target')
                ->grantRight('CyclicSource');
        });

        $right = $rights->getRightByID('CyclicSource');

        $grants = $right->resolveGrants()->getIDs();

        // The "CyclicSource" right must not be present, but the
        // "CyclicExtension" that it grants must be present.
        $this->assertEquals(array('CyclicExtension', 'CyclicTarget'), $grants);
    }

    private function createRightsStructure() : Application_User_Rights
    {
        $rights = new Application_User_Rights();

        $rights->registerGroup('Products', 'Products', function(Application_User_Rights_Group $group)
        {
            $group->registerRight('ViewProduct', 'Viewing')
                ->actionView()
                ->grantRight('ViewProperties');

            $group->registerRight('EditProduct', 'Editing')
                ->actionEdit()
                ->grantRight('ViewProduct')
                ->grantRight('EditProperties');

            $group->registerRight('CreateProduct', 'Creating')
                ->actionCreate()
                ->grantRight('EditProduct');

            $group->registerRight('DeleteProduct', 'Deleting')
                ->actionDelete()
                ->grantRight('EditProduct')
                ->grantRight('CreateProduct');
        });

        // PROPERTIES ------------------------------------------------------------
        // Properties grant access to the property types.

        $rights->registerGroup('Properties', 'Properties', function(Application_User_Rights_Group $group)
        {
            $group->registerRight('ViewProperties', 'Viewing')
                ->actionView()
                ->grantGroupView('PropertyTypes');

            $group->registerRight('EditProperties', 'Edit properties')
                ->actionEdit()
                ->grantRight('ViewProperties')
                ->grantGroupAll('PropertyTypes');
        });

        // CATEGORIES ------------------------------------------------------------

        $rights->registerGroup('Categories', 'Product categories', function(Application_User_Rights_Group $group)
        {
            $group->registerRight('ViewCategory', 'Viewing')
                ->actionView()
                ->grantGroupView('Products');
        });

        // PROPERTY TYPES ------------------------------------------------------------

        $rights->registerGroup('PropertyTypes', 'Property types', function(Application_User_Rights_Group $group)
        {
            $group->registerRight('ViewPropertyType', 'Viewing')
                ->actionView();

            $group->registerRight('EditPropertyType', 'Editing')
                ->actionEdit()
                ->grantRight('ViewPropertyType');

            $group->registerRight('CreatePropertyType', 'Creating')
                ->actionCreate()
                ->grantRight('EditPropertyType');

            $group->registerRight('DeletePropertyType', 'Deleting')
                ->actionDelete()
                ->grantRight('EditPropertyType');
        });

        return $rights;
    }
}
