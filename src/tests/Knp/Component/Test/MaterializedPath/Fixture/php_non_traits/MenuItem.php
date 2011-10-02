<?php

namespace Knp\Component\Test\MaterializedPath\Fixture\php_non_traits;


use Knp\Component\Tree\MaterializedPath\Node;
use Knp\Component\Tree\MaterializedPath\NodeInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class MenuItem implements NodeInterface, \ArrayAccess
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
     * {@inheritdoc}
     **/
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     **/
    public function setPath($path)
    {
        $this->path = $path;

        $this->setParentPath($this->getParentPath());
    }

    /**
     * {@inheritdoc}
     **/
    public function getNodeChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     **/
    public function setNodeChildren(Collection $children)
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     **/
    public function addChild(NodeInterface $node)
    {
        $this->children->add($node);
    }

    /**
     * {@inheritdoc}
     **/
    public function isChildOf(NodeInterface $node)
    {
        return $this->getParentPath() === $node->getPath();
    }

    /**
     * {@inheritdoc}
     **/
    public function setChildOf(NodeInterface $node)
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

    /**
     * {@inheritdoc}
     **/
    public function getParentPath()
    {
        $path = $this->getExplodedPath();
        \array_pop($path);

        $parent_path = \implode(self::PATH_SEPARATOR, $path);

        return $parent_path ?: self::PATH_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     **/
    public function setParentPath($path)
    {
        $this->parent_path = $path;
    }

    /**
     * {@inheritdoc}
     **/
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     **/
    public function setParent(NodeInterface $node)
    {
        $this->parent = $node;
    }

    /**
     * {@inheritdoc}
     **/
    public function getExplodedPath()
    {
        return \explode(self::PATH_SEPARATOR, $this->getPath());
    }

    /**
     * {@inheritdoc}
     **/
    public function getLevel()
    {
        return \count($this->getExplodedPath()) - 1;
    }

    /**
     * Set sort.
     *
     * @param sort the value to set.
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * {@inheritdoc}
     **/
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * {@inheritdoc}
     **/
    public function getRootPath()
    {
        $explodedPath = $this->getExplodedPath();
        array_shift($explodedPath); // first is empty

        return self::PATH_SEPARATOR . array_shift($explodedPath);
    }

    /**
     * {@inheritdoc}
     **/
    public function getRoot()
    {
        $parent = $this;
        while(null !== $parent->getParent()) {
            $parent = $parent->getParent();
        }

        return $parent;
    }

    /**
     * {@inheritdoc}
     **/
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

    /**
     * @param \Closure $prepare a function to preapre the node before putting into the result
     * @param array $tree a reference to an array, used internally for recursion
     *
     * @return array the flatten result
     **/
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

    public function offsetSet($offset, $node)
    {
        $node->setChildOf($this);
    }

    public function offsetExists($offset)
    {
        return isset($this->children[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->children[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->children[$offset];
    }
}

