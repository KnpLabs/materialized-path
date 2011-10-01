<?php

namespace Knp\Component\Test\MaterialzedPath;

use Knp\Component\Test\MaterialzedPath\Fixture\php_non_traits\MenuItem;

abstract class NodeTest extends \PHPUnit_Framework_TestCase
{
    private function buildTree()
    {
        $item = $this->buildNode();
        $item->setPath('/1');
        $item->setId(1);

        $childItem = $this->buildNode();
        $childItem->setPath('/1/2');
        $childItem->setId(2);
        $childItem->setChildOf($item);

        $secondChildItem = $this->buildNode();
        $secondChildItem->setPath('/1/3');
        $secondChildItem->setId(3);
        $secondChildItem->setChildOf($item);

        $childChildItem = $this->buildNode();
        $childChildItem->setId(4);
        $childChildItem->setPath('/1/2/4');
        $childChildItem->setChildOf($childItem);

        $childChildChildItem = $this->buildNode();
        $childChildChildItem->setId(5);
        $childChildChildItem->setPath('/1/2/4/5');
        $childChildChildItem->setChildOf($childChildItem);

        return $item;
    }

    public function testBuildTree()
    {
        $root = $this->buildNode(array('setPath' => '/0'     , 'setName' => 'root'        , 'setId' => 0));
        $flatTree = new \ArrayObject(array(
            $this->buildNode(array('setPath' => '/0/1'       , 'setName' => 'Villes'      , 'setId' => 1)) ,
            $this->buildNode(array('setPath' => '/0/1/2'     , 'setName' => 'Nantes'      , 'setId' => 2)) ,
            $this->buildNode(array('setPath' => '/0/1/2/3'   , 'setName' => 'Nantes Est'  , 'setId' => 3)) ,
            $this->buildNode(array('setPath' => '/0/1/2/4'   , 'setName' => 'Nantes Nord' , 'setId' => 4)) ,
            $this->buildNode(array('setPath' => '/0/1/2/4/5' , 'setName' => 'St-Mihiel'   , 'setId' => 5)) ,
        ));

        $root->buildTree($flatTree);

        $this->assertEquals(1, $root->getNodeChildren()->count());
        $this->assertEquals(1, $root->getNodeChildren()->get(0)->getNodeChildren()->count());
        $this->assertEquals(2, $root->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->count());

        $this->assertEquals(1, $root->getLevel());
        $this->assertEquals(4, $root->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getLevel());
    }

    public function testChildrenCount()
    {
        $tree = $this->buildTree();

        $this->assertEquals(2, $tree->getNodeChildren()->count());
        $this->assertEquals(1, $tree->getNodeChildren()->get(0)->getNodeChildren()->count());
    }

    public function testGetPath()
    {
        $tree = $this->buildTree();

        $this->assertEquals('/1', $tree->getPath());
        $this->assertEquals('/1/2', $tree->getNodeChildren()->get(0)->getPath());
        $this->assertEquals('/1/2/4', $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getPath());
        $this->assertEquals('/1/2/4/5', $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getPath());

        $childChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $childChildChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $childChildItem->setChildOf($tree);
        $this->assertEquals('/1/4', $childChildItem->getPath(), 'The path has been updated fo the node');
        $this->assertEquals('/1/4/5', $childChildChildItem->getPath(), 'The path has been updated fo the node and all its descendants');
        $this->assertTrue($tree->getNodeChildren()->contains($childChildItem), 'The children collection has been updated to reference the moved node');
    }

    public function testMoveChildren()
    {
        $tree = $this->buildTree();

        $childChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $childChildChildItem = $tree->getNodeChildren()->get(0)->getNodeChildren()->get(0)->getNodeChildren()->get(0);
        $this->assertEquals(4, $childChildChildItem->getLevel(), 'The level is well calcuated');

        $childChildItem->setChildOf($tree);
        $this->assertEquals('/1/4', $childChildItem->getPath(), 'The path has been updated fo the node');
        $this->assertEquals('/1/4/5', $childChildChildItem->getPath(), 'The path has been updated fo the node and all its descendants');
        $this->assertTrue($tree->getNodeChildren()->contains($childChildItem), 'The children collection has been updated to reference the moved node');

        $this->assertEquals(3, $childChildChildItem->getLevel(), 'The level has been updated');
    }
}

