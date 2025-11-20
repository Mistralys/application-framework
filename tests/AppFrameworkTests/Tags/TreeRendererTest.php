<?php
/**
 * @package Tagging
 * @subpackage Tests
 */

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;
use UI\Tree\TreeRenderer;

/**
 * The tree renderer is used to display a tree structure.
 *
 * @package Tagging
 * @subpackage Tests
 * @see TreeRenderer
 */
final class TreeRendererTest extends TaggingTestCase
{
    public function test_findNodeByValue() : void
    {
        $root = $this->tagsCollection->createNewTag('Root');

        $A = $root->addSubTag('A');
        $A->addSubTag('A.1');
        $A2 = $A->addSubTag('A.2');
        $A2->addSubTag('A.2.1');

        $renderer = $root->createTreeRenderer();

        $rootTree = $renderer->getRootNode();
        $rootChildren = $rootTree->getChildNodes();
        $this->assertSame('Root', $rootTree->getLabel());
        $this->assertCount(1, $rootChildren);
        $this->assertSame('A', $rootChildren[0]->getLabel());
        $this->assertCount(2, $rootChildren[0]->getChildNodes());
    }
}
