<?php

namespace Eccube\Tests\Entity;

use Eccube\Paginator\AbstractPaginator;
use Knp\Component\Pager\PaginatorInterface;

/**
 * AbstractEntity test cases.
 *
 * @author Yasumasa Yoshinaga
 */
class AbstractPaginatorTest extends \PHPUnit_Framework_TestCase
{
    private $objEntity;

    public function testNewInstance()
    {
        $this->objEntity = new TestPaginator(new TestConcreateComponent());
        $this->assertTrue(is_object($this->objEntity));
    }

    public function testNewInstanceEmptyParams()
    {
        try {
            $this->objEntity = new TestPaginator();
            $this->assertTrue(is_object($this->objEntity));
        } catch (Exception $e){
            $assertEquals($e->getMessage(), 'Not given paginator object,this class constructor must be need object.');
        }
    }

    public function testAllSetter()
    {
        $target = array('0' => new Object());
        $items = array('a','b','c','d','e');
        $total = 15;
        $page = 2;
        $limit = 3;
        $options('hoge' => 0, 'huga' => 1);

        $this->objEntity = new TestPaginator(new TestConcreateComponent());
        $this->objEntity->setItemTarget($target);
        $this->objEntity->setPaginationTarget($target);
        $this->objEntity->setPaginationViewItems($items);
        $this->objEntity->setPaginationViewTotal($total);
        $this->objEntity->paginate($target, $page , $limit, $options);

        $concreateComponent = $this->getPaginate();

        $this->assertEquals($concreateComponent->getItemTarget(), $this->objEntity->setItemTarget());
        $this->assertEquals($concreateComponent->getPaginationTarget(), $this->objEntity->setPaginationTarget());
        $this->assertEquals($concreateComponent->getPaginationViewItems(), $this->objEntity->getPaginationViewItems());
        $this->assertEquals($concreateComponent->getItemTarget(), $this->objEntity->setItemTarget());
        $this->assertEquals($concreateComponent->getPaginationViewTotal(), $this->objEntity->getPaginationViewTotal());
        $concreatePaginate = $concreateComponent->getPaginate();
        $objEntityPaginate = $this->objEntity->getItemTarget();
        $this->assertEquals($concreatePaginate['target'], $objEntityPaginate['target']);
        $this->assertEquals($concreatePaginate['items'], $objEntityPaginate['items']);
        $this->assertEquals($concreatePaginate['total'], $objEntityPaginate['total']);
        $this->assertEquals($concreatePaginate['limit'], $objEntityPaginate['limit']);
        $this->assertEquals($concreatePaginate['options'], $objEntityPaginate['options']);
    }
}

class TestPaginator extends AbstractPaginator
{
    public function __construct(\Knp\Component\Pager\PaginatorInterface $paginator)
    {
        parent::__construct($paginator);
    }

    public function getPaginator()
    {
        return $this->paginator;
    }

    public function setItemTarget($target)
    {
        $this->paginator->setItemTarget($target);
    }

    public function setPaginationTarget($target)
    {
        $this->paginator->setPaginationTarget($target);
    }

    public function setPaginationViewItems($items)
    {
        $this->paginator->setPaginationViewItems($items);
    }

    public function setPaginationViewTotal($total)
    {
        $this->paginator->setPaginationViewTotal($total);
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        $this->paginator->paginate($target, $page, $limit, $options);
    }

    public function getPaginateView()
    {
        return $this->paginator->getPaginateView();
    }

    public function getItemTarget()
    {
        return $this->paginator->getItemTarget();
    }

    public function getPaginationTarget()
    {
        return $this->paginator->getPaginationTarget();
    }

    public function getPaginationViewItems()
    {
        return $this->paginator->getPaginationViewItems();
    }

    public function setPaginationViewTotal()
    {
        return $this->paginator->getPaginationViewTotal();
    }

    public function getPaginate()
    {
        return $this->paginator->getPaginate();
    }
}

class TestConcreateComponent implements PaginatorInterface
{
    private $itemTarget;
    private $paginationTarget;
    private $paginationViewItems;
    private $paginationViewTotal;
    private $paginate;
    private $paginateView;

    public function __construct(){
        $this->paginateView = 'paginateView';
    }

    public function setItemTarget($target)
    {
        $this->itemTarget($target);
    }

    public function setPaginationTarget($target)
    {
        $this->paginationTarget($target);
    }

    public function setPaginationViewItems($items)
    {
        $this->paginationViewItems($items);
    }

    public function setPaginationViewTotal($total)
    {
        $this->paginationViewTotal($total);
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        $this->paginate = array('target' => $target, 'page' => $page, 'limit' => $limit,'options' => $options);
    }

    public function gettemTarget()
    {
        return $this->itemTarget;
    }

    public function getPaginationTarget()
    {
        return $this->paginationTarget;
    }

    public function getPaginationViewItems()
    {
        return $this->paginationViewItems;
    }

    public function getPaginationViewTotal()
    {
        return $this->paginationViewTotal;
    }

    public function getPaginate()
    {
        return $this->paginate;
    }

    public function getPaginateView()
    {
        return $this->paginateView;
    }
}
