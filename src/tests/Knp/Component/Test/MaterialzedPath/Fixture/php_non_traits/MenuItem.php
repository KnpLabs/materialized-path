<?php

namespace Knp\Component\Test\MaterialzedPath\Fixture\php_non_traits;


use Knp\Component\Tree\MaterialzedPath\Node;
use Knp\Component\Tree\MaterialzedPath\NodeInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class MenuItem implements NodeInterface
{
    const PATH_SEPARATOR = '/';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @param Collection the children in the tree
     */
    private $children;

    /**
     * @param NodeInterface the parent in the tree
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sort;

    public function __construct()
    {
        $this->children = new ArrayCollection;
    }

    public function __toString()
    {
        return (string) $this->name;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string
     * @return null
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string
     * @return null
     */
    public function setName($name)
    {
        $this->name = $name;
    }

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

    public function getNodeChildren()
    {
        return $this->children;
    }

    public function setNodeChildren(Collection $children)
    {
        $this->children = $children;
    }

    public function addChild(NodeInterface $node)
    {
        $this->children->add($node);
    }

    /**
     * @return boolean
     */
    public function isChildOf(NodeInterface $item)
    {
        return $this->getParentPath() === $item->getPath();
    }

    public function setChildOf(NodeInterface $item)
    {
        $id = $this->getId();
        if (empty($id)) {
            throw new \LogicException('You must provide an id for this node if you want it to be part of a tree.');
        }
        $this->setPath($item->getPath() . self::PATH_SEPARATOR . $this->getId());

        if (null !== $this->parent) {
            $this->parent->getNodeChildren()->removeElement($this);
        }

        $this->parent = $item;
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

        return $parent_path;
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
        foreach($results as $item) {
            $parentPath = $item->getParentPath();
            if(!isset($tree[$parentPath])) {
                $tree[$parentPath] = $this;
            }

            $tree[$parentPath]->addChild($item);

            if(!isset($tree[$item->getPath()])) {
                $tree[$item->getPath()] = $item;
            }
        }
    }

}

