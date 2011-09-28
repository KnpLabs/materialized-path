<?php

namespace Knp\Component\Tree\MaterialzedPath;

use Doctrine\Common\Collections\Collection;

/**
 * Tree\Node defines a set of needed mathods
 * to work with materialized path tree nodes
 *
 * @author     Florian Klein <florian.klein@free.fr>
 */
interface NodeInterface
{
    /**
     * @return string the materialized path,
     * eg the representation of path from all ancestors
     **/
    function getPath();

    /**
     * @return string the materialized path from the parent, eg: the representation of path from all parent ancestors
     **/
    function getParentPath();


    /**
     * @return NodeInterface the parent node
     **/
    function getParent();

    /**
     * @param string $path the materialized path, eg: the the materialized path to its parent
     *
     * @return NodeInterface $this Fluent interface
     **/
    function setPath($path);

    /**
     * Used to build the hierarchical tree.
     * This method will do:
     *    - modify the parent of this node
     *    - Add the this node to the children of the new parent
     *    - Remove the this node from the children of the old parent
     *    - Modify the materialized path of this node and all its children, recursively
     *
     * @param NodeInterface $node The node to use as a parent
     *
     * @return NodeInterface $this Fluent interface
     **/
    function setChildOf(NodeInterface $node);

    /**
     * @param Collection the children collection
     *
     * @return NodeInterface $this Fluent interface
     **/
    function setNodeChildren(Collection $children);

    /**
     * @param NodeInterface the node to append to the children collection
     *
     * @return NodeInterface $this Fluent interface
     **/
    function addChild(NodeInterface $node);

    /**
     * @return Collection the children collection
     **/
    function getNodeChildren();

    /**
     * Tells if this node is a child of another node
     * @param NodeInterface $node the node to compare with
     *
     * @return boolean true if this node is a direct child of $node
     **/
    function isChildOf(NodeInterface $node);

    /**
     * @param integer|mixed the value used to sort nodes in current level
     *
     * @return NodeInterface $this Fluent interface
     **/
    function setSort($sort);

    /**
     *
     * @return integer|mixed the value used to sort nodes in current level
     **/
    function getSort();

    /**
     *
     * @return integer the level of this node, eg: the depth compared to root node
     **/
    function getLevel();

    /**
     * Builds a hierarchical tree from a flat collection of NodeInterface elements
     *
     * @return void
     **/
    function buildTree(\Traversable $nodes);
}

