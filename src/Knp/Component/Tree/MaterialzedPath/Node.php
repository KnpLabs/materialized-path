<?php

namespace Knp\Component\Tree\MaterialzedPath;

use Knp\Component\Tree\MaterialzedPath\NodeInterface as TreeNodeInterface;

use Doctrine\Common\Collections\Collection;

/*
 * @author     Florian Klein <florian.klein@free.fr>
 */
trait Node
{
    /**
     * Get path.
     *
     * @return path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set path.
     *
     * @param path the value to set.
     */
    public function setPath($path)
    {
        $this->path = $path;

        $this->setParentPath($this->getParentPath());
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getNodeChildren()
    {
        return $this->children;
    }

    public function setNodeChildren(Collection $children)
    {
        $this->children = $children;
    }

    public function addChild(TreeNodeInterface $node)
    {
        $this->children->add($node);
    }

    /**
     * @return boolean
     */
    public function isChildOf(TreeNodeInterface $node)
    {
        return $this->getParentPath() === $node->getPath();
    }

    public function setChildOf(TreeNodeInterface $node)
    {
        $id = $this->getId();
        if (empty($id)) {
            throw new \LogicException('You must provide an id for this node if you want it to be part of a tree.');
        }

        $this->setPath($node->getPath() . self::PATH_SEPARATOR . $this->getId());

        if (null !== $this->parent) {
            $this->parent->getNodeChildren()->removeElement($this);
        }

        $this->parent = $node;
        $this->parent->addChild($this);

        foreach($this->getNodeChildren() as $child)
        {
            $child->setChildOf($this);
        }

        return $this;
    }

    public function getParentPath()
    {
        $path = $this->getExplodedPath();
        \array_pop($path);

        $parent_path = \implode(self::PATH_SEPARATOR, $path);

        return $parent_path ?: self::PATH_SEPARATOR;
    }

    /**
     * Set parent path.
     *
     * @param path the value to set.
     */
    public function setParentPath($path)
    {
        $this->parent_path = $path;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(NodeInterface $node)
    {
        $this->parent = $node;
    }

    public function getExplodedPath()
    {
        return \explode(self::PATH_SEPARATOR, $this->getPath());
    }

    public function getLevel()
    {
        return \count($this->getExplodedPath()) - 1;
    }

    /**
     * Get sort.
     *
     * @return sort.
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set sort.
     *
     * @param sort the value to set.
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getRootPath()
    {
        $explodedPath = $this->getExplodedPath();
        array_shift($explodedPath); // first is empty

        return self::PATH_SEPARATOR . array_shift($explodedPath);
    }

    public function getRoot()
    {
        $parent = $this;
        while(null !== $parent) {
            $parent = $parent->getParent();
        }

        return $parent;
    }

    public function buildTree(\Traversable $results)
    {
        $tree = array($this->getPath() => $this);
        foreach($results as $node) {

            $tree[$node->getPath()] = $node;

            $parent = isset($tree[$node->getParentPath()]) ? $tree[$node->getParentPath()] : $this; // root is the fallback parent
            $parent->addChild($node);
            $node->setParent($parent);
        }
    }

    /**
     * @param \Closure $prepare a function to preapre the node before putting into the result
     *
     * @return string the json representation of the hierarchical result
     **/
    public function toJson(\Closure $prepare = null)
    {
        $tree = $this->toArray($prepare);

        return json_encode($tree);
    }

    /**
     * @param \Closure $prepare a function to preapre the node before putting into the result
     * @param array $tree a reference to an array, used internally for recursion
     *
     * @return array the hierarchical result
     **/
    public function toArray(\Closure $prepare = null, array &$tree = null)
    {
        if(null === $prepare) {
            $prepare = function(NodeInterface $node) {
                return (string)$node;
            };
        }
        if (null === $tree) {
            $tree = array($this->getId() => array('node' => $prepare($this), 'children' => array()));
        }

        foreach($this->getNodeChildren() as $node) {
            $tree[$this->getId()]['children'][$node->getId()] = array('node' => $prepare($node), 'children' => array());
            $node->toArray($prepare, $tree[$this->getId()]['children']);
        }

        return $tree;
    }

    public function toFlatArray(\Closure $prepare = null, array &$tree = null)
    {
        if(null === $prepare) {
            $prepare = function(NodeInterface $node) {
                $pre = $node->getLevel() > 1 ? implode('', array_fill(0, $node->getLevel(), '--')) : '';
                return (string)$node;
            };
        }
        if (null === $tree) {
            $tree = array($this->getId() => $prepare($this));
        }

        foreach($this->getNodeChildren() as $node) {
            $tree[$node->getId()] = $prepare($node);
            $node->toFlatArray($prepare, $tree);
        }

        return $tree;
    }
}
