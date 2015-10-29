<?php

namespace Eccube\Paginator;

use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Component\Pager\Event;
use Eccube\Paginator\Paginator;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Paginator uses event dispatcher to trigger pagination
 * lifecycle events. Subscribers are expected to paginate
 * wanted target and finally it generates pagination view
 * which is only the result of paginator
 */
class AbstaractPagnator implements PaginatorInterface
{
    protected $paginator;

    /**
     * this method works can be parent class.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */

    public function __construct($paginator)
    {
        $this->paginator = $paginator;
    }

    public function getPaginator(){
        return $this->paginator;
    }

    public function setItemTarget($target){
        $this->paginator->setItemTarget($target);
    }

    public function setPaginationTarget($target){
        $this->paginator->setPaginationTarget($target);
    }

    public function setPaginationViewItems($items){
        $this->paginator->setPaginationViewItems($items);
    }

    public function setPaginationViewTotal($total){
        $this->paginator->setPaginationViewTotal = $total;
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array()){
        $this->paginator->paginate($target, $page, $limit, $options);
    }

    public function getPaginateView(){
        return $this->paginator->getPaginateView();
    }
}
