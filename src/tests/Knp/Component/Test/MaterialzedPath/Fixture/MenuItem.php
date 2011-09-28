<?php

namespace Knp\Component\Test\MaterialzedPath\Fixture;


use Knp\Component\Tree\MaterialzedPath\Node;
use Knp\Component\Tree\MaterialzedPath\NodeInterface as TreeNodeInterface;

use Doctrine\Common\Collections\ArrayCollection;


class MenuItem implements TreeNodeInterface
{
    const PATH_SEPARATOR = '/';

    use Node {

    }

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
}

