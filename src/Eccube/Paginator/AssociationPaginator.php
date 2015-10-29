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
class AssociationPaginator extends AbstaractPagnator
{
    public function __construct($paginator)
    {
        parent::__construct($paginator);
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        $this->limit = $limit = intval(abs($limit));
        $this->page = $page;
        if (!$limit) {
            throw new \LogicException("Invalid item per page number, must be a positive number");
        }
        $offset = abs($page - 1) * $limit;
        $counter = clone $target;
        $counter->resetDQLpart('orderBy');
        $counter->select('count(p)');
        $total = count($target->getQuery()->getScalarResult());
        //クエリビルダーにリミット・オフセット付与
        $target->setFirstResult($offset)
            ->setMaxResults($limit);

        //検索結果取得
        $query_res = $target->getQuery()->getResult();
        $items = array();

        //不要なセレクト句を排除
        for($i = 0; $i < count($query_res); $i++){
            //var_dump($query_res[$i]);
            $items[] = $query_res[$i][0];
        }

        parent::setItemTarget($items);
        parent::setPaginationTarget($items);
        parent::paginate($items, $page, $limit, $options);
        parent::setPaginationViewItems($items);
        parent::setPaginationViewTotal($total);
        return parent::getPaginateView();
    }
}
