<?php

namespace Knp\Component\Test\MaterialzedPath\Fixture\php_non_traits;


use Knp\Component\Tree\MaterialzedPath\Node;
use Knp\Component\Tree\MaterialzedPath\NodeInterface as TreeNodeInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class MenuItem implements TreeNodeInterface
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

    /**
     * Locale
     * @ORM\ManyToOne(targetEntity="VPAutoBundle\Entity\Locale", fetch="EAGER")
     */
    private $locale;

    /**
     * target
     *
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $target;

    /**
     * link
     *
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * Page
     * @ORM\ManyToOne(targetEntity="VPAutoBundle\Entity\Cms\Page", fetch="EAGER")
     */
    private $page;

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
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param  string
     * @return null
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    public function setInternalPage(Page $page = null)
    {
        $this->page = $page;
    }

    public function getInternalPage()
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param  string
     * @return null
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param  Locale $locale
     * @return null
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
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

    public function getOptions()
    {
        $options = array(
            'uri'    => $this->link,
            'attributes' => array(
                'id'          => 'menu_'.$this->id,
                'data-id'     => $this->id,
            ),
            'menu'   => $this,
        );
        if(null !== $this->getInternalPage()) {
            $options['route']  = 'frontend_page_view';
            $options['routeParameters'] = array('slug' => $this->getInternalPage()->getSlug());
        }

        return $options;
    }

    /**
     * @ORM\postLoad
     **/
    public function postLoad()
    {
        if (null === $this->children) {
            $this->children = new ArrayCollection;
        }
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
    public function isChildOf(TreeNodeInterface $item)
    {
        return $this->getParentPath() === $item->getPath();
    }

    public function setChildOf(TreeNodeInterface $item)
    {
        $id = $this->getId();
        if (empty($id)) {
            throw new \LogicException('You must provide an id for this node if you want it to be part of a tree.');
        }
        $this->setPath($item->getPath() . self::PATH_SEPARATOR . $this->getId());

        if (null !== $this->parent) {
            $this->parent->getChildren()->removeElement($this);
        }

        $this->parent = $item;
        $this->parent->addChild($this);

        foreach($this->getChildren() as $child)
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

