# Materialized paths for PHP 5.4


Imagine you want to manage a tree the OO way without beeing tied to any ORM.

This library wants to be simple, you'll only need to use a ``Trait`` and provide a flat resultSet.

If you don't have Traits, [do as the compiler](https://wiki.php.net/rfc/horizontalreuse#static_methods), 
ie: copy'n'paste the trait content in your class.


## Usage:

* **Define a class that implements ``NodeInterface``**:

You'll notice i'm using a Doctrine Entity to implement the NodeInterface,
but you could use any plain old php class.

```php

    <?php

    namespace Knp\Component\Test\MaterializedPath\Fixture;


    use Knp\Component\Tree\MaterializedPath\Node;
    use Knp\Component\Tree\MaterializedPath\NodeInterface;

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;


    /**
    * @ORM\Entity
    * @ORM\Table(name="cms_menu")
    * @ORM\Entity(repositoryClass="Entity\MenuItemRepository")
    * @ORM\HasLifecycleCallbacks
    */
    class MenuItem implements NodeInterface
    {
        const PATH_SEPARATOR = '/';

        // traits baby!
        // if your php version doesn't support traits, copy paste the methods of Knp\Component\Tree\MaterializedPath\Node
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
         * @var Collection the children in the tree
         */
        private $children;

        /**
         * @var NodeInterface the parent in the tree
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
    }

```


* **Build a hierarchical tree**:

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

* **Get some flat data**:

Something like that:

```

     id | locale_id |   name    |    path    | sort |
    ----+-----------+-----------+------------+------+
      1 | fr        | fr        | /fr        |      |
      2 |           | villes    | /fr/2      |      |
      4 |           | subNantes | /fr/2/3/4  |      |
      7 | en        | en        | /en        |      |
      8 |           | villes    | /en/8      |      |
      9 |           | Nantes    | /en/8/9    |      |
     10 |           | subNantes | /en/8/9/10 |      |
     11 |           | Lorient   | /en/8/11   |      |
     12 |           | Rouen     | /en/8/12   |      |
      6 |           | Rouen     | /fr/2/6    |    1 |
      3 |           | Nantes    | /fr/2/3    |    0 |
      5 |           | Lorient   | /fr/2/5    |    2 |

```

* **Use it**:

```php

    <?php

    //$repo is the EntityRepository defined above.
    $menu = $repo->find($id);
    $repo->buildTree($menu);

    $parent = $repo->find($parentId);
    $menu->setChildOf($parent);

    $em->persist($menu);
    $em->flush();

    // you have ArrayAccess too
    $tree[0][1][2] === $tree->getNodeChildren()->get(0)->getNodeChildren()->get(1)->getNodeChildren()->get(2); // true

```

To find more usages, you can look at [the tests](https://github.com/knplabs/materialized-path/blob/master/src/tests/Knp/Component/Test/MaterializedPath/NodeTest.php).

