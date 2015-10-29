<?php

namespace Eccube\Paginator;

use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Component\Pager\Event;
use Eccube\Paginator\Paginator;
use Eccube\Paginator\AbstaractPagnator;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Paginator uses event dispatcher to trigger pagination
 * lifecycle events. Subscribers are expected to paginate
 * wanted target and finally it generates pagination view
 * which is only the result of paginator
 */
class DefaultPaginator extends AbstaractPagnator
{
    public function __construct($paginator)
    {
        parent::__construct($paginator);
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        parent::setItemTarget($target);
        parent::setPaginationTarget($target);
        parent::paginate($target, $page, $limit, $options);
        parent::setPaginationViewItems(parent::getPaginator()->getItemsEvent()->items);
        parent::setPaginationViewTotal(parent::getPaginator()->getItemsEvent()->count);
        return parent::getPaginateView();
    }
}
