<?php

namespace Knp\Component\Test\Tree\MaterializedPath\php_non_traits;

use Knp\Component\Test\MaterializedPath\Fixture\php_non_traits\MenuItem;
use Knp\Component\Test\MaterializedPath\NodeTest as BaseNodeTest;

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

