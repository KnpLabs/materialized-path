== Materialized paths for PHP 5.4 ==

Imagine you want to manage a tree the OO way without beeing tied to any ORM.

This library wants to be simple, you'll only need to use a Trait and provide a flat resultset.

If you don't have Traits, [do as the compiler](https://wiki.php.net/rfc/horizontalreuse#static_methods), 
ie: copy'n'paste the trait content in your class.


Usage:

* Define a class that implements ``NodeInterface``:

You'll notice i'm using a Doctrine Entity to implement the NodeInterface,
but you could use any plain old php class.

```php

    <?php

    namespace Knp\Component\Test\MaterialzedPath\Fixture;


    use Knp\Component\Tree\MaterialzedPath\Node;
    use Knp\Component\Tree\MaterialzedPath\NodeInterface as TreeNodeInterface;

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;


    /**
    * @ORM\Entity
    * @ORM\Table(name="cms_menu")
    * @ORM\Entity(repositoryClass="Entity\MenuItemRepository")
    * @ORM\HasLifecycleCallbacks
    */
    class MenuItem implements TreeNodeInterface
    {
        const PATH_SEPARATOR = '/';

        // traits baby!
        // if your php version doesn't support traits, copy paste the methods of Knp\Component\Tree\MaterialzedPath\Node
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
         * @ORM\postLoad
         **/
        public function postLoad()
        {
            if (null === $this->children) {
                $this->children = new ArrayCollection;
            }
        }
    }


```


* Get a hierarchical tree:

I'm currently using a Doctrine ORM repository to build a flat representation of my tree.


```php

    <?php

    namespace Entity;

    class MenuItemRepository extends EntityRepository
    {
        public function getRootNodes()
        {
            $qb = $this->createQueryBuilder('m')
                ->andWhere('m.path NOT LIKE :path')
                ->setParameter('path', '/%/%')
            ;

            return $qb->getQuery()->execute();
        }

        public function buildTree(MenuItem $root)
        {
            $qb = $this->createQueryBuilder('m')
                ->andWhere('m.path LIKE :path')
                ->andWhere('m.id != :id')
                ->addOrderBy('m.path', 'ASC')
                ->addOrderBy('m.sort', 'ASC')
                ->setParameter('path', $root->getPath().'%')
                ->setParameter('id', $root->getId())
            ;
            $results = $qb->getQuery()->execute();

            $root->buildTree(new \ArrayObject($results));
        }
    }

```


* Use it:

```php

    <?php

    //$repo is the EntityRepository defined above.
    $menu = $repo->find($id);
    $repo->buildTree($menu);

    $parent = $repo->find($parentId);
    $menu->setChildOf($parent);

    $em->persist($menu);
    $em->flush();

```

