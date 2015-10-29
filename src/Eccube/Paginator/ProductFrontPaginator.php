<?php

namespace Eccube\Paginator;

use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Component\Pager\Event;
use Eccube\Paginator\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber;

/**
 * Paginator uses event dispatcher to trigger pagination
 * lifecycle events. Subscribers are expected to paginate
 * wanted target and finally it generates pagination view
 * which is only the result of paginator
 */
class ProductFrontPaginator extends Paginator implements PaginatorInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Initialize paginator with event dispatcher
     * Can be a service in concept. By default it
     * hooks standard pagination subscriber
     * @marks : if you will make original paginator,you must be need set eventDispatcher here.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct()
    {
        if(is_null($this->eventDispatcher)){
            $this->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
            $this->eventDispatcher->addSubscriber(new \Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber());
            $this->eventDispatcher->addSubscriber(new \Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber());
        }
    }

    /**
     * Override the default paginator options
     * to be reused for paginations
     *
     * @param array $options
     */
    public function setDefaultPaginatorOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
    }

    /**
     * Paginates anything (depending on event listeners)
     * into Pagination object, which is a view targeted
     * pagination object (might be aggregated helper object)
     * responsible for the pagination result representation
     *
     * @param mixed $target - anything what needs to be paginated
     * @param integer $page - page number, starting from 1
     * @param integer $limit - number of items per page
     * @param array $options - less used options:
     *     boolean $distinct - default true for distinction of results
     *     string $alias - pagination alias, default none
     *     array $whitelist - sortable whitelist for target fields being paginated
     * @throws \LogicException
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        //オーダー判定用SQL抽出
        $check_order_sql = $target->getDQL();

        //オーダー種別判定
        $price_order_flg = false;
        if(preg_match('/price02/', $check_order_sql)){
            $price_order_flg = true;
        }

        $limit = intval(abs($limit));
        //リミット作成
        if (!$limit) {
            throw new \LogicException("Invalid item per page number, must be a positive number");
        }

        //オフセット作成
        $offset = abs($page - 1) * $limit;

        //価格昇順ページネート用設定
        if($price_order_flg){
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
        }

        //オプション設定共通処理
        $options = array_merge($this->defaultOptions, $options);

        // normalize default sort field
        if (isset($options['defaultSortFieldName']) && is_array($options['defaultSortFieldName'])) {
            $options['defaultSortFieldName'] = implode('+', $options['defaultSortFieldName']);
        }

        // default sort field and direction are set based on options (if available)
        if (!isset($_GET[$options['sortFieldParameterName']]) && isset($options['defaultSortFieldName'])) {
            $_GET[$options['sortFieldParameterName']] = $options['defaultSortFieldName'];

            if (!isset($_GET[$options['sortDirectionParameterName']])) {
                $_GET[$options['sortDirectionParameterName']] = isset($options['defaultSortDirection']) ? $options['defaultSortDirection'] : 'asc';
            }
        }

        // before pagination start
        $beforeEvent = new \Knp\Component\Pager\Event\BeforeEvent($this->eventDispatcher);
        $this->eventDispatcher->dispatch('knp_pager.before', $beforeEvent);

        // items
        $itemsEvent = new \Knp\Component\Pager\Event\ItemsEvent($offset, $limit);
        $itemsEvent->options = &$options;
        if($price_order_flg){
            //価格
            $itemsEvent->target = &$items;
        }else{
            //新着
            $itemsEvent->target = &$target;
        }

        $this->eventDispatcher->dispatch('knp_pager.items', $itemsEvent);
        if (!$itemsEvent->isPropagationStopped()) {
            throw new \RuntimeException('One of listeners must count and slice given target');
        }
        $paginationEvent = new \Knp\Component\Pager\Event\PaginationEvent();
        if($price_order_flg){
            //価格
            $paginationEvent->target = &$items;
        }else{
            //新着
            $paginationEvent->target = &$target;
        }

        $paginationEvent->options = &$options;
        $this->eventDispatcher->dispatch('knp_pager.pagination', $paginationEvent);
        if (!$paginationEvent->isPropagationStopped()) {
            throw new \RuntimeException('One of listeners must create pagination view');
        }
        // pagination class can be different, with different rendering methods
        $paginationView = $paginationEvent->getPagination();
        $paginationView->setCustomParameters($itemsEvent->getCustomPaginationParameters());
        $paginationView->setCurrentPageNumber($page);
        $paginationView->setItemNumberPerPage($limit);
        if($price_order_flg){
            //価格
            $paginationView->setTotalItemCount($total);
        }else{
            //新着
            $paginationView->setTotalItemCount($itemsEvent->count);
        }
        $paginationView->setPaginatorOptions($options);
        if($price_order_flg){
            //価格
            $paginationView->setItems($items);
        }else{
            //新着
            $paginationView->setItems($itemsEvent->items);
        }

        // after
        $afterEvent = new \Knp\Component\Pager\Event\AfterEvent($paginationView);
        $this->eventDispatcher->dispatch('knp_pager.after', $afterEvent);
        return $paginationView;
    }

    /**
     * Hooks in the given event subscriber
     *
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function subscribe(Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * Hooks the listener to the given event name
     *
     * @param string $eventName
     * @param object $listener
     * @param integer $priority
     */
    public function connect($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }
}
