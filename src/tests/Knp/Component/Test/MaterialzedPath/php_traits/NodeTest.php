<?php

namespace Knp\Component\Test\Tree\MaterialzedPath\php_traits;

use Knp\Component\Test\MaterialzedPath\Fixture\MenuItem;
use Knp\Component\Test\MaterialzedPath\NodeTest as BaseNodeTest;

class NodeTest extends BaseNodeTest
{
    protected function buildNode(array $values = array())
    {
        $node = new MenuItem;
        foreach($values as $method => $value) {
            $node->$method($value);
        }

        return $node;
    }
}

