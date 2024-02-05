<?php
/**
 * @package Application Tests
 * @subpackage Tagging
 */

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;
use UI\Tree\TreeRenderer;

/**
 * The tree renderer is used to display a tree structure.
 *
 * @package Application Tests
 * @subpackage Tagging
 * @see TreeRenderer
 */
final class TreeRendererTests extends TaggingTestCase
{
    public function test_findNodeByValue() : void
    {
        $root = $this->tagsCollection->createNewTag('Root');

        $root->addSubTag('A');
    }
}
