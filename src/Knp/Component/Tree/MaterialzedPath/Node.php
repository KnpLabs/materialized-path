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
        if (empty($this->getId())) {
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

        return $parent_path ?: '/';
    }

    public function getParent()
    {
        return $this->parent;
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

    public function buildTree(\Traversable $results)
    {
        $tree = array($this->getPath() => $this);
        foreach($results as $node) {

            $tree[$node->getPath()] = $node;

            $tree[$node->getParentPath()]->addChild($node);
        }
    }
}
